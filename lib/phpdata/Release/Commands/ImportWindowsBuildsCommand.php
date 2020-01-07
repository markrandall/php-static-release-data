<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release\Commands;
	
	use GuzzleHttp\Client;
	use GuzzleHttp\RequestOptions;
	use phpdata\Release\Release;
	use phpdata\Release\WindowsBuild;
	use phpdata\Release\WindowsBuildDependency;
	use phpdata\Release\WindowsBuildFile;
	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	
	class ImportWindowsBuildsCommand extends Command
	{
		public const SAFETY_TYPES = [
			'ts'  => 'Thread-Safe',
			'nts' => 'Non Thread Safe',
		];
		
		public const PLATFORM_TYPES = [
			'x86' => 'Win32',
			'x64' => 'Win64',
		];
		
		public function configure() {
			$this
				->setName('releases:import-windows-builds')
				->setDescription('Import source and build information for Windows');
		}
		
		public function execute(InputInterface $input, OutputInterface $output) {
			/* releases json data from website */
			$win_releases_json_str = (new Client())->get(
				'https://windows.php.net/downloads/releases/releases.json',
				[
					RequestOptions::HEADERS => [
						'User-Agent' => 'marandall@php.net doing web dev stuff',
					],
				]
			)->getBody()->getContents();
			
			$win_releases_json = json_decode($win_releases_json_str, false, 512, JSON_THROW_ON_ERROR);
			
			foreach ($win_releases_json as $branch_id => $branch_json) {
				$ver_id = $branch_json->version;
				
				$release = Release::LoadVersion($ver_id);
				if (!$release) {
					continue;
				}
				
				$output->writeln('Found release ' . $release->getVersionString());
				
				$builds = [];
				foreach ($branch_json as $build_key => $files) {
					
					if (strpos($build_key, 'ts-') !== 0 && strpos($build_key, 'nts-') !== 0) {
						continue;
					}
					
					$build_files = [];
					foreach ($files as $file_name => $file_info) {
						if (!is_object($file_info)) {
							continue;
						}
						
						$build_files[] = new WindowsBuildFile(
							$file_name,
							$file_info->path,
							(int)$file_info->size * 1024 * 1024,
							$file_info->sha256,
							'https://windows.php.net/downloads/releases/' . $file_info->path
						);
					}
					
					$label_parts    = explode('-', strtolower($build_key));
					$build_platform = self::PLATFORM_TYPES[$label_parts[2]];
					$build_compiler = $label_parts[1];
					
					$dependencies = [];
					if (in_array($label_parts[1], ['vc15', 'vs15'], true)) {
						if ($build_platform === 'Win32') {
							$dependencies[] = new WindowsBuildDependency(
								'x86 Redistributable for Visual Studio 2015-2019',
								'https://aka.ms/vs/16/release/VC_redist.x86.exe'
							);
						}
						else if ($build_platform === 'Win64') {
							$dependencies[] = new WindowsBuildDependency(
								'x64 Redistributable for Visual Studio 2015-2019',
								'https://aka.ms/vs/16/release/VC_redist.x64.exe'
							);
						}
					}
					
					$builds[] = new WindowsBuild(
						$build_key,
						self::SAFETY_TYPES[$label_parts[0]] . ' - ' . $build_platform . ' - ' . $build_compiler,
						$build_files,
						$dependencies
					);
				}
				
				$release->replaceWindowsBuilds($builds);
				$release->save();
			}
			
			return 0;
		}
	}