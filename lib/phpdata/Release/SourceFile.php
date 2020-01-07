<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	use DateTimeImmutable;
	use SimpleXMLElement;
	
	/**
	 * Represents a source code release
	 *
	 * Every release of PHP has its source code distributed in several different
	 * compression formats (bz2, gz, xz).
	 */
	class SourceFile
	{
		private string $name;
		
		private string $filename;
		
		private DateTimeImmutable $date;
		
		private string $sha256;
		
		private string $md5;
		
		public function __construct(string $name, string $filename, DateTimeImmutable $date, string $sha256, string $md5) {
			$this->name     = $name;
			$this->filename = $filename;
			$this->date     = $date;
			$this->sha256   = $sha256;
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
			return $this->sha256;
		}
		
		public function getMd5(): string {
			return $this->md5;
		}
		
		public function toJson(): object {
			return (object)[
				'name'     => $this->name,
				'filename' => $this->filename,
				'date'     => $this->date->format('Y-m-d'),
				'sha256'   => $this->sha256,
				'md5'      => $this->md5,
			];
		}
		
		public static function FromXmlElement(SimpleXMLElement $element): self {
			return new SourceFile(
				(string)$element->name ?: '',
				(string)$element->filename ?: '',
				new DateTimeImmutable((string)$element->date),
				(string)$element->sha256,
				(string)$element->md5
			);
		}
	}