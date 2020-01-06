<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release\Commands;
	
	use phpdata\Release\Release;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	
	class RemoveTagsConsoleCommand extends AbstractExistingRelease
	{
		public function configure() {
			parent::configure();
			
			$this
				->setName('release:remove-tags')
				->setDescription('Removes tags from the release');
			
			$this->addArgument('tags', InputArgument::IS_ARRAY | InputArgument::REQUIRED);
		}
		
		public function executeWithRelease(InputInterface $input, OutputInterface $output, Release $release) {
			$tags = $input->getArgument('tags');
			foreach ($tags as $tag) {
				$output->writeln('Removing tag: ' . $tag);
				$release->removeTag($tag);
			}
		}
	}