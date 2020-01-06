<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release\Commands;
	
	use DomainException;
	use phpdata\Release\Release;
	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	
	abstract class AbstractExistingRelease extends Command
	{
		public function configure() {
			parent::configure();
			
			$this
				->addArgument('version', InputArgument::REQUIRED, 'Release version in form x.y.z');
		}
		
		public function execute(InputInterface $input, OutputInterface $output) {
			$version = $input->getArgument('version');
			
			try {
				$release = Release::LoadVersion($version);
			}
			catch (DomainException $ex) {
				$output->writeln('Cannot read the release file');
				$output->writeln($ex->getMessage());
				return 1;
			}
			
			
			$res = $this->executeWithRelease($input, $output, $release);
			if (is_int($res) && $res !== 0) {
				return $res;
			}
			
			$release->save();
			return 0;
		}
		
		abstract public function executeWithRelease(InputInterface $input, OutputInterface $output, Release $release);
	}