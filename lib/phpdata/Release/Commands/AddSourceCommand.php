<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release\Commands;
	
	use DateTimeImmutable;
	use Exception;
	use phpdata\Release\Release;
	use phpdata\Release\Source;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;
	
	class AddSourceCommand extends AbstractExistingRelease
	{
		public function configure() {
			parent::configure();
			
			$this
				->setName('release:add-source')
				->setDescription('Add a new source file to this release');
			
			$this->addArgument('name', InputArgument::REQUIRED, 'Descriptive name of the file');
			$this->addArgument('filename', InputArgument::REQUIRED, 'Filename of the file');
			$this->addArgument('sha256', InputArgument::REQUIRED, 'SHA256 of the file');
			$this->addOption('date', '', InputOption::VALUE_REQUIRED, 'Date of the file', date('Y-m-d'));
			$this->addOption('force', '', InputOption::VALUE_NONE, 'Should this be applied if an existing source exists');
		}
		
		public function executeWithRelease(InputInterface $input, OutputInterface $output, Release $release) {
			$name     = $input->getArgument('name');
			$filename = $input->getArgument('filename');
			$sha256   = $input->getArgument('sha256');
			$date     = $input->getOption('date');
			
			try {
				$date_time = new DateTimeImmutable($date);
			}
			catch (Exception $ex) {
				$output->writeln('Cannot parse the date');
				return 1;
			}
			
			$existing = $release->getSource($filename);
			if ($existing && !$input->getOption('force')) {
				$output->writeln('Source with filename ' . $filename . ' already exists. To overwrite use --force.');
				return 1;
			}
			
			$source = new Source($name, $filename, $date_time, $sha256, '');
			$release->addSource($source);
			
			$output->writeln('Added source file');
		}
	}