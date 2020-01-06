<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release\Commands;
	
	use phpdata\Release\Change;
	use phpdata\Release\ChangeModule;
	use phpdata\Release\ChangeReference;
	use phpdata\Release\Release;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	
	class ImportNewsCommand extends AbstractExistingRelease
	{
		public function configure() {
			parent::configure();
			
			$this
				->setName('release:import-news')
				->setDescription('Imports the NEWS file to generate the list of changes');
			
			$this->addArgument(
				'path', InputArgument::REQUIRED,
				'Path to the NEWS file, if the path is "github" it will be fetched from github'
			);
		}
		
		public function executeWithRelease(InputInterface $input, OutputInterface $output, Release $release) {
			$path = $input->getArgument('path');
			if ($path === 'github') {
				$github_uri = 'https://raw.githubusercontent.com/php/php-src/PHP-' . $release->getVersionString(
					) . '/NEWS';
				
				$contents = @file_get_contents($github_uri);
				if (!$contents) {
					$output->writeln('Cannot download NEWS file from ' . $github_uri);
					return 1;
				}
			}
			else {
				$contents = @file_get_contents($path);
				if (!$contents) {
					$output->writeln('Cannot read NEWS file from ' . $path);
					return 1;
				}
			}

			$lines      = explode("\n", str_replace("\r", "", $contents));
			
			$i          = 0;
			$line_count = count($lines);
			
			$module  = '';
			$changes = [];
			
			$inside = false;
			while ($i < $line_count) {
				$ln = $lines[$i];
				
				if (preg_match("/(.. ... ....),? PHP " . $release->getVersionString() . "/", $ln, $m)) {
					$inside = true;
					$date   = strtr($m[1], " ", "-");
					$i++;
				}
				else if ($inside && preg_match("/(.. ... ....),/", $ln, $m)) {
					break;
				}
				else if (strpos($ln, '- ') === 0) {
					$module = strtolower(substr($ln, 2, -1));
					$i++;
				}
				else if ($ln === '') {
					$i++;
				}
				else if (strpos($ln, '  . ') === 0) {
					$message = '';
					do {
						$message .= substr($lines[$i], 4); // removes both padding and the "  . "
						$i++;
					} while ($i < $line_count && strpos($lines[$i], '    ') === 0);
					
					$cm = [
						'description' => '',
						'references'  => [],
					];
					
					if (preg_match('/Fixed bug #([0-9]+)/', $message, $m)) {
						$cm['references'][] = ['type' => 'bugfix', 'value' => $m[1]];
						$message = str_replace($m[0], '', $message);
					}
					
					if (preg_match('/Fixed PECL bug #([0-9]+)/', $message, $m)) {
						$cm['references'][] = ['type' => 'pecl-bugfix', 'value' => $m[1]];
						$message = str_replace($m[0], '', $message);
					}
					
					if (preg_match('/Implemented FR #([0-9]+)/', $message, $m)) {
						$cm['references'][] = ['type' => 'feature-request', 'value' => $m[1]];
						$message = str_replace($m[0], '', $message);
					}
					
					if (preg_match('/\(CVE-\d+-\d+\)/', $message, $m)) {
						$cm['references'][] = ['type' => 'cve', 'value' => substr($m[0], 1, -1)];
						$message = str_replace($m[0], '', $message);
					}
					
					if (preg_match('/(\.(\s+\(CVE-\d+-\d+\))?)\s+\(.+?\)\s*$/', $message, $m)) {
						$author_str = $m[0] ?? '';
						if ($author_str) {
							$message = str_replace($m[0], '', $message);
							
							if (preg_match('/\((.+?)\)/', $author_str, $av)) {
								$authors = explode(',', $av[1]);
								foreach ($authors as $author) {
									$cm['references'][] = ['type' => 'author', 'value' => trim($author)];
								}
							}
						}
					}
					
					
					$cm['description'] = trim($message, "\t ");
					$changes[$module][] = $cm;
				}
				else {
					$i++;
				}
			}
			
			$all_changed_modules = [];
			foreach ($changes as $module_id => $module_changes) {
				$changes_for_module = [];

				$output->writeln('Module = ' . $module_id);
				foreach ($module_changes as $module_change) {
					$output->writeln('  * ' . $module_change['description']);

					$refs = [];
					foreach ($module_change['references'] as $reference) {
						$output->writeln('     ' . $reference['type'] . ' = ' . $reference['value']);
						$refs[] = new ChangeReference($reference['type'], $reference['value']);
					}

					$changes_for_module[] = new Change($module_change['description'], $refs);
				}

				$all_changed_modules[] = new ChangeModule($module_id, $changes_for_module);
				$output->writeln('');
			}
			
			 $release->setChangedModules($all_changed_modules);
		}
	}