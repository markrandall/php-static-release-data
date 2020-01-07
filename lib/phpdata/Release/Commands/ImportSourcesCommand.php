<?php
	
	declare(strict_types=1);
	
	
	namespace phpdata\Release\Commands;
	
	use DateTimeImmutable;
	use phpdata\Release\Release;
	use phpdata\Release\SourceFile;
	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	
	class ImportSourcesCommand extends Command
	{
		public function configure() {
			parent::configure();
			
			$this
				->setName('releases:import-sources')
				->setDescription('Import source information from php.net');
		}
		
		private function fetchRemote(): array {
			$versions = [3, 4, 5, 7];
			$releases = [];
			
			foreach ($versions as $major_version) {
				$json = json_decode(
					file_get_contents(
						'https://www.php.net/releases/index.php?json=1&max=-1&version=' . $major_version
					)
				);
				
				foreach ($json as $ver => $data) {
					$releases[$ver] = $data;
				}
			}
			
			return $releases;
		}
		
		public function execute(InputInterface $input, OutputInterface $output) {
			foreach ($this->fetchRemote() as $version_id => $data) {
				$release = Release::LoadVersion($version_id);
				if (!$release) {
					continue;
				}
				
				$output->writeln('Found ' . $release->getVersionString());
				
				$source_list = [];
				foreach ($data->source as $source) {
					if (!isset($source->filename)) {
						continue;
					}
					
					$source_list[] = new SourceFile(
						$source->name,
						$source->filename,
						new DateTimeImmutable($source->date ?? '2001-01-01'),
						$source->sha256 ?? '',
						$source->md5 ?? ''
					);
				}
				
				$release->setSources($source_list);
				$release->save();
			}
			
			return 0;
		}
	}