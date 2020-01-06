<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	class ChangeReference
	{
		private string $type;
		
		private string $data;
		
		public function __construct(string $type, string $data) {
			$this->type = $type;
			$this->data = $data;
		}
		
		public function getType(): string {
			return $this->type;
		}
		
		public function getData(): string {
			return $this->data;
		}
		
		public function toJson(): object {
			return (object)['type' => $this->type, 'value' => $this->data];
		}
	}