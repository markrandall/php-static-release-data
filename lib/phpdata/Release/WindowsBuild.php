<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	use SimpleXMLElement;
	
	/**
	 * Each version of PHP is released as 4 separate builds
	 *
	 * These are usually:
	 *   x64  x86
	 *    ts  nts
	 */
	
	class WindowsBuild
	{
		/** @var string - This is the internal build */
		private string $build_name;
		
		/** @var string -  This is what is shown to the user */
		private string $label;
		
		/** @var array|WindowsBuildFile[] */
		private array $files;
		
		/** @var WindowsBuildDependency[] */
		private array $dependencies;
		
		/**
		 * @param WindowsBuildFile[]       $files
		 * @param WindowsBuildDependency[] $dependencies
		 */
		public function __construct(
			string $build_name,
			string $label,
			array $files = [],
			array $dependencies = []
		) {
			$this->build_name   = $build_name;
			$this->label        = $label;
			$this->files        = $files;
			$this->dependencies = $dependencies;
		}
		
		/**
		 * @return string
		 */
		public function getBuildName(): string {
			return $this->build_name;
		}
		
		/**
		 * @return string
		 */
		public function getLabel(): string {
			return $this->label;
		}
		
		/**
		 * @return array|WindowsBuildFile[]
		 */
		public function getFiles() {
			return $this->files;
		}
		
		/**
		 * @return WindowsBuildDependency[]
		 */
		public function getDependencies(): array {
			return $this->dependencies;
		}
		
		public function writeToElement(SimpleXMLElement $element) {
			$element->addAttribute('name', $this->build_name);
			$element->addChild('label', $this->label);
			
			$xml_files = $element->addChild('files');
			foreach ($this->files as $file) {
				$file->writeToElement($xml_files->addChild('file'));
			}
			
			$xml_dependencies = $element->addChild('dependencies');
			foreach ($this->dependencies as $dependency) {
				$dependency->writeToElement($xml_dependencies->addChild('dependency'));
			}
		}
		
		public static function FromXmlElement(SimpleXMLElement $element): WindowsBuild {
			$files = [];
			foreach ($element->files->file as $xml_file) {
				$files[] = WindowsBuildFile::FromXmlElement($xml_file);
			}
			
			$dependencies = [];
			foreach ($element->dependencies->dependency as $xml_dependency) {
				$dependencies[] = WindowsBuildDependency::FromXmlElement($xml_dependency);
			}
			
			return new self(
				(string)$element->attributes()->name,
				(string)$element->label,
				$files,
				$dependencies
			);
		}
		
		public function toJson(): object {
			return (object)[
				'name'         => $this->build_name,
				'label'        => $this->label,
				'files'        => array_map(fn(WindowsBuildFile $file) => $file->toJson(), $this->files),
				'dependencies' => array_map(fn(WindowsBuildDependency $dep) => $dep->toJson(), $this->dependencies),
			];
		}
	}