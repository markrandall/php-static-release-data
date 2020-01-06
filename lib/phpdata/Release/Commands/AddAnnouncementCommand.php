<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release\Commands;
	
	use phpdata\Release\Release;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;
	
	class AddAnnouncementCommand extends AbstractExistingRelease
	{
		public function configure() {
			parent::configure();
			
			$this
				->setName('release:add-announcement')
				->setDescription('Add or replace an announcement message for this release');
			
			$this->addOption('lang', '', InputOption::VALUE_REQUIRED, 'Language code', 'en');
			$this->addOption('force', '', InputOption::VALUE_NONE, 'Overwrite if the announcement already exists');
			
			$this->addArgument('file', InputArgument::REQUIRED, 'Path to the announcement file');
		}
		
		public function executeWithRelease(InputInterface $input, OutputInterface $output, Release $release) {
			$announcement_file = $input->getArgument('file');
			if (!file_exists($announcement_file) || !is_readable($announcement_file)) {
				$output->writeln('Cannot open announcement file ' . $announcement_file);
				return 1;
			}
			
			$lang = $input->getOption('lang');
			
			$existing = $release->getAnnouncement($lang);
			if ($existing !== null && !$input->getOption('force')) {
				$output->writeln('Announcement for ' . $lang . ' already exists. To overwrite use --force.');
				return 1;
			}
			
			$output->writeln('Adding the announcement file');
			$release->setAnnouncementForLanguage($lang, str_replace("\r", "", file_get_contents($announcement_file)));
		}
	}