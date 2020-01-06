<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	class Announcement
	{
		private string $lang;
	
		private string $content;
		
		public function __construct(string $lang, string $content) {
			$this->lang = $lang;
			$this->content = $content;
		}
		
		public function getLang(): string {
			return $this->lang;
		}
		
		public function getContent(): string {
			return $this->content;
		}
	}
	