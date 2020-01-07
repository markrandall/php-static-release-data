<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	use SimpleXMLElement;
	
	class Change
	{
		private string $description;
		
		/** @var ChangeReference[] */
		private array $references;
		
		public function __construct(string $description, array $references) {
			$this->description = $description;
			$this->references  = $references;
		}
		
		/**
		 * @return string
		 */
		public function getDescription(): string {
			return $this->description;
		}
		
		/**
		 * @return ChangeReference[]
		 */
		public function getReferences(): array {
			return $this->references;
		}
		
		public function toJson(): object {
			$refs = [];
			foreach ($this->references as $reference) {
				$refs[] = $reference->toJson();
			}
			
			return (object)[
				'description' => $this->description,
				'references'  => $refs,
			];
		}
		
		public static function FromXmlElement(SimpleXMLElement $element): self {
			$refs = [];
			foreach ($element->references->reference as $ref) {
				$refs[] = new ChangeReference(
					(string)$ref->attributes()->type,
					(string)$ref
				);
			}
			
			return new Change((string)$element->description, $refs);
		}
	}