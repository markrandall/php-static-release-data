<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Branch\Commands;
	
	use DateTimeImmutable;
	use Exception;
	use phpdata\Branch\Branch;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;
	
	class ModifyDatesCommand extends AbstractBranchCommand
	{
		public function configure() {
			parent::configure();
			
			$this
				->setName('branch:modify-dates')
				->setDescription('Modifies the eol dates on a branch');
			
			$this
				->addOption('eol', '', InputOption::VALUE_REQUIRED, 'EOL Date')
				->addOption('eol-security', '', InputOption::VALUE_REQUIRED, 'EOL Security Date');
		}
		
		protected function executeWithBranch(InputInterface $input, OutputInterface $output, Branch $branch) {
			$eol_str = $input->getOption('eol');
			if ($eol_str) {
				if ($eol_str === 'none') {
					$branch->setEol(null);
				}
				else {
					try {
						$branch->setEol(new DateTimeImmutable($eol_str));
						$output->writeln('Setting EOL date to ' . $branch->getEol()->format('Y-m-d'));
					}
					catch (Exception $ex) {
						$output->writeln('Unable to parse the EOL date; ' . $ex->getMessage());
						return 1;
					}
				}
			}
			
			$eol_security_str = $input->getOption('eol-security');
			if ($eol_security_str) {
				if ($eol_security_str === 'none') {
					$branch->setEolSecurity(null);
				}
				else {
					try {
						$branch->setEolSecurity(new DateTimeImmutable($eol_security_str));
						$output->writeln('Setting EOL security date to ' . $branch->getEolSecurity()->format('Y-m-d'));
					}
					catch (Exception $ex) {
						$output->writeln('Unable to parse the EOL security date; ' . $ex->getMessage());
						return 1;
					}
				}
			}
		}
		
	}