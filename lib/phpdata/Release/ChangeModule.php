<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	class ChangeModule
	{
		private string $module_id;
		
		/** @var Change[] */
		private array $changes;
		
		/**
		 * @param string   $module_id
		 * @param Change[] $changes
		 */
		public function __construct(string $module_id, array $changes) {
			$this->module_id = $module_id;
			$this->changes   = $changes;
		}
		
		/**
		 * @return Change[]
		 */
		public function getChanges(): array {
			return $this->changes;
		}
		
		/**
		 * @return string
		 */
		public function getModuleId(): string {
			return $this->module_id;
		}
		
		public function toJson(): array {
			$changes = [];
			foreach ($this->changes as $change) {
				$changes[] = $change->toJson();
			}
			
			return $changes;
		}
	}