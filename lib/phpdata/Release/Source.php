<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	use DateTimeImmutable;
	
	class Source
	{
		private string $name;
		
		private string $filename;
		
		private DateTimeImmutable $date;
		
		private string $sha1;
		
		private string $md5;
		
		public function __construct(string $name, string $filename, DateTimeImmutable $date, string $sha1, string $md5) {
			$this->name     = $name;
			$this->filename = $filename;
			$this->date     = $date;
			$this->sha1     = $sha1;
			$this->md5      = $md5;
		}
		
		public function getName(): string {
			return $this->name;
		}
		
		public function getFilename(): string {
			return $this->filename;
		}
		
		public function getDate(): DateTimeImmutable {
			return $this->date;
		}
		
		public function getSha256(): string {
			return $this->sha1;
		}
		
		public function getMd5(): string {
			return $this->md5;
		}
	}