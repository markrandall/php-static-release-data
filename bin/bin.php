<?php
	
	use phpdata\Branch\Commands\ModifyDatesCommand;
	use phpdata\Release\Commands\AddAnnouncementCommand;
	use phpdata\Release\Commands\AddSourceCommand;
	use phpdata\Release\Commands\AddTagsConsoleCommand;
	use phpdata\Release\Commands\CreateReleaseCommand;
	use phpdata\Release\Commands\ImportNewsCommand;
	use phpdata\Release\Commands\RemoveTagsConsoleCommand;
	use Symfony\Component\Console\Application;
	
	if (function_exists('opcache_reset')) {
		opcache_reset();
	}
	
	require_once __DIR__ . '/../lib/vendor/autoload.php';
	
	$app = new Application('PHP Web Data Updater');
	$app->addCommands(
		[
			/* create release */
			new CreateReleaseCommand(),
			
			/* modify release */
			new AddAnnouncementCommand(),
			new AddSourceCommand(),
			new ImportNewsCommand(),
			new AddTagsConsoleCommand(),
			new RemoveTagsConsoleCommand(),
			
			/* modify branch */
			new ModifyDatesCommand(),
		]
	);
	
	$app->run();