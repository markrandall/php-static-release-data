<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release\Commands;
	
	use phpdata\Release\Release;
	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	
	class CreateReleaseCommand extends Command
	{
		public function configure() {
			$this
				->setName('release:create')
				->setDescription('Create or update a release file');
			
			$this
				->addArgument('version', InputArgument::REQUIRED, 'Release version in form x.y.z');
		}
		
		protected function execute(InputInterface $input, OutputInterface $output) {
			$release_id = trim($input->getArgument('version'));
			if (!$release_id) {
				$output->writeln('Release ID is missing or invalid');
			}
			
			$path = Release::PathFromVersion($release_id);
			if (file_exists($path)) {
				$output->writeln('Release file already exists. Please use other commands instead.');
				return 1;
			}
			
			$release = Release::CreateVersion($release_id);
			$release->save();
			
			$output->writeln('Release created');
			return 0;
		}
	}