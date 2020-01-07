<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Release;
	
	use DateTimeImmutable;
	use DomainException;
	use phpdata\Tools\XmlHelpers;
	use SimpleXMLElement;
	
	class Release
	{
		private string $version;
		
		private DateTimeImmutable $date;
		
		/** @var string[] */
		private array $tags = [];
		
		/** @var Announcement[] */
		private array $announcements = [];
		
		/** @var Source[] */
		private array $sources = [];
		
		/** @var ChangeModule[] */
		private array $changed_modules = [];
		
		public static function PathFromVersion(string $ver): string {
			$seg     = explode('.', $ver);
			$major   = $seg[0];
			$minor   = $seg[1];
			$release = $seg[2];
			
			return realpath(__DIR__ . '/../../../public/releases/' . $major . '_' . $minor . '/')
				. DIRECTORY_SEPARATOR . $major . '_' . $minor . '_' . $release . '.xml';
		}
		
		public static function CreateVersion(string $version): Release {
			$release          = new Release();
			$release->version = $version;
			$release->date    = new DateTimeImmutable('now');
			return $release;
		}
		
		public static function LoadVersion(string $version): ?Release {
			return self::LoadFromFile(self::PathFromVersion($version));
			
		}
		
		public static function LoadFromFile(string $path): Release {
			if (!file_exists($path) || !is_readable($path)) {
				throw new DomainException('Cannot open ' . $path);
			}
			
			$xml = simplexml_load_string(file_get_contents($path));
			if (!$xml) {
				throw new DomainException('Cannot parse ' . $path . '; ' . error_get_last()['message']);
			}
			
			return self::FromXml($xml);
		}
		
		public static function FromXml(SimpleXMLElement $xml): Release {
			$release          = new Release();
			$release->version = (string)$xml->attributes()->version;
			$release->date    = new DateTimeImmutable((string)$xml->date);
			
			foreach ($xml->announcements->announcement as $announcement) {
				$release->setAnnouncementForLanguage(
					(string)$announcement->attributes()->lang,
					(string)$announcement
				);
			}
			
			foreach ($xml->sources->source as $xml_source) {
				$source = new Source(
					(string)$xml_source->name ?: '',
					(string)$xml_source->filename ?: '',
					new DateTimeImmutable((string)$xml_source->date),
					(string)$xml_source->sha256,
					(string)$xml_source->md5
				);
				
				$release->sources[$source->getFilename()] = $source;
			}
			
			/* bc upgrade fix */
			$xml_module_changes = $xml->changes->module->count()
				? $xml->changes->module
				: $xml->changes->modules->module;
			
			foreach ($xml_module_changes as $module) {
				$changes = [];
				
				foreach ($module->change as $change) {
					$refs = [];
					foreach ($change->references->reference as $ref) {
						$refs[] = new ChangeReference(
							(string)$ref->attributes()->type,
							(string)$ref
						);
					}
					
					$changes[] = new Change((string)$change->description, $refs);
				}
				
				$release->changed_modules[] = new  ChangeModule((string)$module->attributes()->id, $changes);
			}
			
			foreach ($xml->tags->tag as $tag) {
				$release->tags[] = (string)$tag;
			}
			
			return $release;
		}
		
		public function toXml(): SimpleXMLElement {
			$ee = function (string $data): string {
				return htmlspecialchars($data, ENT_NOQUOTES);
			};
			
			
			$element = new SimpleXMLElement('<release></release>');
			$element->addAttribute('version', $this->getVersionString());
			$element->addChild('version', $this->getVersionString());
			$element->addChild('date', $this->date->format('Y-m-d'));
			
			$sources = $element->addChild('sources');
			foreach ($this->sources as $source) {
				$xml_source = $sources->addChild('source');
				$xml_source->addChild('name', $ee($source->getName()));
				$xml_source->addChild('filename', $ee($source->getFilename()));
				$xml_source->addChild('date', $ee($source->getDate()->format('Y-m-d')));
				
				if ($source->getSha256()) {
					$xml_source->addChild('sha256', $ee($source->getSha256()));
				}
				else {
					$xml_source->addChild('md5', $ee($source->getMd5()));
				}
			}
			
			$xml_announcements = $element->addChild('announcements');
			foreach ($this->announcements as $announcement) {
				$xml_announcement = $xml_announcements->addChild('announcement', $ee($announcement->getContent()));
				$xml_announcement->addAttribute('lang', $announcement->getLang());
			}
			
			$xml_changes = $element->addChild('changes')->addChild('modules');
			foreach ($this->changed_modules as $module) {
				$xml_module = $xml_changes->addChild('module');
				$xml_module->addAttribute('id', strtolower($module->getModuleId()));
				
				foreach ($module->getChanges() as $change) {
					$xml_change = $xml_module->addChild('change');
					$xml_change->addChild('description', $ee($change->getDescription()));
					$xml_refs = $xml_change->addChild('references');
					
					foreach ($change->getReferences() as $reference) {
						$xml_refs->addChild('reference', $ee($reference->getData()))->addAttribute(
							'type', $reference->getType()
						);
					}
				}
			}
			
			$xml_tags = $element->addChild('tags');
			foreach ($this->tags as $tag) {
				$xml_tags->addChild('tag', $ee($tag));
			}
			
			return $element;
		}
		
		public function toJson(): object {
			$announcements = [];
			foreach ($this->announcements as $announcement) {
				$announcements[$announcement->getLang()] = $announcement->getContent();
			}
			
			$changes = [];
			foreach ($this->changed_modules as $module) {
				$changes['modules'][strtolower($module->getModuleId())] = $module->toJson();
			}
			
			$sources = [];
			foreach ($this->sources as $source) {
				$sources[] = $source->toJson();
			}
			
			return (object)[
				'version'       => $this->getVersionString(),
				'date'          => $this->date->format('Y-m-d'),
				'changes'       => $changes,
				'announcements' => $announcements,
				'tags'          => $this->tags,
				'source'        => $sources,
			];
		}
		
		public function save() {
			$xml = $this->toXml();
			
			$xml_str  = XmlHelpers::SimpleXmlToFormatted($xml);
			$xml_path = self::PathFromVersion($this->version);
			
			file_put_contents($xml_path, $xml_str);
		}
		
		public function getVersionString(): string {
			return $this->version;
		}
		
		/**
		 * Sets or replaces an announcement for a given language
		 *
		 * @return $this
		 */
		public function setAnnouncementForLanguage(string $lang, string $text) {
			$this->announcements[$lang] = new Announcement($lang, $text);
			return $this;
		}
		
		/**
		 * @param string $lang
		 * @return Announcement|null
		 */
		
		public function getAnnouncement(string $lang): ?Announcement {
			return $this->announcements[$lang] ?? null;
		}
		
		/**
		 * @return string[]
		 */
		public function getTags(): array {
			return $this->tags;
		}
		
		public function addTag(string $tag) {
			if (!in_array($tag, $this->tags, true)) {
				$this->tags[] = $tag;
			}
		}
		
		public function removeTag(string $tag) {
			$this->tags = array_filter($this->tags, fn($val) => $val !== $tag);
		}
		
		/**
		 * @return Announcement[]
		 */
		public function getAnnouncements(): array {
			return $this->announcements;
		}
		
		/**
		 * @return Source[]
		 */
		public function getSources(): array {
			return $this->sources;
		}
		
		public function addSource(Source $source) {
			$this->sources[$source->getFilename()] = $source;
			return $this;
		}
		
		public function getSource(string $filename): ?Source {
			return $this->sources[$filename] ?? null;
		}
		
		/**
		 * @return ChangeModule[]
		 */
		public function getChangedModules(): array {
			return $this->changed_modules;
		}
		
		
		/**
		 * @param string $version
		 * @return $this
		 */
		public function setVersion(string $version) {
			$this->version = $version;
			return $this;
		}
		
		/**
		 * @param DateTimeImmutable $date
		 * @return $this
		 */
		public function setDate(DateTimeImmutable $date) {
			$this->date = $date;
			return $this;
		}
		
		/**
		 * @param string[] $tags
		 * @return $this
		 */
		public function setTags(array $tags) {
			$this->tags = $tags;
			return $this;
		}
		
		/**
		 * @param Announcement[] $announcements
		 * @return $this
		 */
		public function setAnnouncements(array $announcements) {
			$this->announcements = $announcements;
			return $this;
		}
		
		/**
		 * @param Source[] $sources
		 * @return $this
		 */
		public function setSources(array $sources) {
			$this->sources = $sources;
			return $this;
		}
		
		/**
		 * @param ChangeModule[] $changed_modules
		 * @return $this
		 */
		public function setChangedModules(array $changed_modules) {
			$this->changed_modules = $changed_modules;
			return $this;
		}
	}