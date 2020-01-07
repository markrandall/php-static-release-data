<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	use SimpleXMLElement;
	
	/**
	 * Most windows builds have additional requirements for the compiler runtime which
	 * must be installed in order for PHP to run.
	 *
	 * This object represents such a dependency
	 */
	class WindowsBuildDependency
	{
		private string $label;
		
		private string $url;
		
		public function __construct(string $label, string $url) {
			$this->label = $label;
			$this->url   = $url;
		}
		
		public function writeToElement(SimpleXMLElement $element) {
			$element->addChild('label', $this->label);
			$element->addChild('url', $this->url);
		}
		
		public static function FromXmlElement(SimpleXMLElement $element): WindowsBuildDependency {
			return new WindowsBuildDependency(
				(string)$element->label,
				(string)$element->url
			);
		}
		
		public function toJson(): object {
			return (object)[
				'label' => $this->label,
				'url'   => $this->url,
			];
		}
		
		public function getLabel(): string {
			return $this->label;
		}
		
		public function getUrl(): string {
			return $this->url;
		}
	}