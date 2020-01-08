<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Tools\Commands;
	
	use phpdata\Branch\Branch;
	use phpdata\Release\Release;
	use phpdata\Tools\XmlHelpers;
	use SimpleXMLElement;
	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	
	class CompileCommand extends Command
	{
		public function configure() {
			parent::configure();
			
			$this
				->setName('compile')
				->setDescription('Compiles all XML files into their JSON equivalents');
		}
		
		
		public function execute(InputInterface $input, OutputInterface $output) {
			$base_path = realpath(__DIR__ . '/../../../../public/releases');
			
			$all_branches = [];
			$xml_releases = new SimpleXMLElement('<branches></branches>');
			
			foreach (scandir($base_path, SCANDIR_SORT_ASCENDING) as $branch_name) {
				$branch_path = $base_path . DIRECTORY_SEPARATOR . $branch_name;
				if ($branch_name[0] === '.' || !is_dir($branch_path)) {
					continue;
				}
				
				$branch_ver = str_replace('_', '.', $branch_name);
				
				$xml_branch = $xml_releases->addChild('branch');
				$xml_branch->addAttribute('id', (string)$branch_ver);
				
				$xml_branch_releases = $xml_branch->addChild('releases');
				$branch_xml_path     = $branch_path . DIRECTORY_SEPARATOR . 'branch.xml';
				
				printf("Branch info = %s\n", $branch_xml_path);
				
				if (file_exists($branch_xml_path)) {
					$branch = Branch::LoadFromPath($branch_xml_path);
					
					if ($branch->getEol()) {
						$xml_branch->addChild('eol', $branch->getEol()->format('Y-m-d'));
						$all_branches[$branch_ver]['eol'] = $branch->getEol()->format('Y-m-d');
					}
					
					if ($branch->getEolSecurity()) {
						$xml_branch->addChild('eol_security', $branch->getEolSecurity()->format('Y-m-d'));
						$all_branches[$branch_ver]['eol_security'] = $branch->getEolSecurity()->format('Y-m-d');
					}
				}
				
				
				foreach (glob($branch_path . DIRECTORY_SEPARATOR . '?_?_*.xml') as $xml_path) {
					$release = Release::LoadFromFile($xml_path);
					$release->save();
					
					$rc = $xml_branch_releases
						->addChild(
							'release', $branch_name . '/' . str_replace('.', '_', $release->getVersionString()) . '.xml'
						);
					
					$rc->addAttribute('version', $release->getVersionString());
					$rc->addAttribute('sha256', hash_file('sha256', Release::PathFromVersion($release->getVersionString())));
					
					$all_branches[$branch_ver]['releases'][$release->getVersionString()] = $release->toJson();
				}
			}
			
			$json_str  = json_encode($all_branches, JSON_PRETTY_PRINT);
			$json_path = __DIR__ . '/../../../../public/releases/releases.json';
			
			if (!file_exists($json_path) || file_get_contents($json_path) !== $json_str) {
				$output->writeln('Writing to ' . $json_path);
				file_put_contents($json_path, $json_str);
			}
			
			$xml_path = __DIR__ . '/../../../../public/releases/releases.xml';
			$xml_str  = XmlHelpers::SimpleXmlToFormatted($xml_releases);
			
			if (!file_exists($xml_path) || file_get_contents($xml_path) !== $xml_str) {
				$output->writeln('Writing to ' . $xml_path);
				file_put_contents($xml_path, $xml_str);
			}
			
			return 0;
		}
	}