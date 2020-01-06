<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Branch\Commands;
	
	use DomainException;
	use phpdata\Branch\Branch;
	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	
	abstract class AbstractBranchCommand extends Command
	{
		public function configure() {
			parent::configure();
			$this->addArgument('version', InputArgument::REQUIRED, 'Branch identifier (e.g. 7.4)');
		}
		
		public function execute(InputInterface $input, OutputInterface $output) {
			$ver_str = $input->getArgument('version');
			
			try {
				$branch = Branch::LoadVersion($ver_str);
			}
			catch (DomainException $ex) {
				$output->writeln('Cannot load branch; ' . $ex->getMessage());
				return 1;
			}
			
			$result = $this->executeWithBranch($input, $output, $branch);
			if (is_int($result) && $result !== 0) {
				return $result;
			}
			
			$branch->save();
			return 0;
		}
		
		abstract protected function executeWithBranch(InputInterface $input, OutputInterface $output, Branch $branch);
	}