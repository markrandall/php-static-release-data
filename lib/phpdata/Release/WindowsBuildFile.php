<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	use SimpleXMLElement;
	
	/**
	 * This is a specific file that exists as part of a particular build
	 *
	 * There will usually be one for the zip, one for the debug_pack and one for devel_pack
	 */
	class WindowsBuildFile
	{
		private string $name;
		
		private string $path;
		
		private int $size;
		
		private string $sha256;
		
		private string $url;
		
		public function __construct(string $name, string $path, int $size, string $sha256, string $url) {
			$this->name   = $name;
			$this->path   = $path;
			$this->size   = $size;
			$this->sha256 = $sha256;
			$this->url    = $url;
		}
		
		public function getName(): string {
			return $this->name;
		}
		
		public function getPath(): string {
			return $this->path;
		}
		
		public function getSize(): int {
			return $this->size;
		}
		
		public function getSha256(): string {
			return $this->sha256;
		}
		
		public function getUrl(): string {
			return $this->url;
		}
		
		public function writeToElement(SimpleXMLElement $element) {
			$element->addAttribute('name', $this->name);
			$element->addChild('path', $this->path);
			$element->addChild('size', (string)$this->size);
			$element->addChild('sha256', $this->sha256);
			$element->addChild('url', $this->url);
		}
		
		public function toJson(): object {
			return (object)[
				'name'   => $this->name,
				'path'   => $this->path,
				'size'   => $this->size,
				'sha256' => $this->sha256,
				'url'    => $this->url,
			];
		}
		
		public static function FromXmlElement(SimpleXMLElement $element): WindowsBuildFile {
			return new self(
				(string)$element->attributes()->name,
				(string)$element->path,
				(int)(string)$element->size,
				(string)$element->sha256,
				(string)$element->url
			);
		}
	}