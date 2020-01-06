<?php
	
	declare(strict_types=1);
	
	namespace phpdata\Branch;
	
	use DateTimeImmutable;
	use DomainException;
	use phpdata\Tools\XmlHelpers;
	use SimpleXMLElement;
	
	class Branch
	{
		/**
		 * @return string
		 */
		public function getVersionString(): string {
			return $this->version_string;
		}
		
		/**
		 * @param string $version_string
		 * @return $this
		 */
		public function setVersionString(string $version_string) {
			$this->version_string = $version_string;
			return $this;
		}
		
		/**
		 * @return DateTimeImmutable|null
		 */
		public function getEol(): ?DateTimeImmutable {
			return $this->eol;
		}
		
		/**
		 * @param DateTimeImmutable|null $eol
		 * @return $this
		 */
		public function setEol(?DateTimeImmutable $eol) {
			$this->eol = $eol;
			return $this;
		}
		
		/**
		 * @return DateTimeImmutable|null
		 */
		public function getEolSecurity(): ?DateTimeImmutable {
			return $this->eol_security;
		}
		
		/**
		 * @param DateTimeImmutable|null $eol_security
		 * @return $this
		 */
		public function setEolSecurity(?DateTimeImmutable $eol_security) {
			$this->eol_security = $eol_security;
			return $this;
		}
		
		private string $version_string;
		
		private ?DateTimeImmutable $eol = null;
		
		private ?DateTimeImmutable $eol_security = null;
		
		public function __construct(
			string $version_string,
			?DateTimeImmutable $eol,
			?DateTimeImmutable $eol_security
		) {
			$this->version_string = $version_string;
			$this->eol            = $eol;
			$this->eol_security   = $eol_security;
		}
		
		public static function VersionToPath(string $version): string {
			$seg   = explode('.', $version);
			$major = $seg[0];
			$minor = $seg[1];
			
			return realpath(__DIR__ . '/../../../public/releases/' . $major . '_' . $minor . '/')
				. DIRECTORY_SEPARATOR . 'branch.xml';
		}
		
		public static function Create(string $version): Branch {
			return new Branch($version, null, null);
		}
		
		public static function LoadVersion(string $version): Branch {
			$path = self::VersionToPath($version);
			
			if (!file_exists($path) || !is_readable($path)) {
				return self::Create($version);
			}
			
			$xml = simplexml_load_string(file_get_contents($path));
			if (!$xml) {
				throw new DomainException('Unable to parse the XML; ' . error_get_last()['message']);
			}
			
			$eol_str = (string)$xml->eol;
			$eol     = $eol_str ? new DateTimeImmutable($eol_str) : null;
			
			$eol_security_str = (string)$xml->eol_security;
			$eol_security     = $eol_security_str ? new DateTimeImmutable($eol_security_str) : null;
			
			return new Branch($version, $eol, $eol_security);
		}
		
		public function save() {
			$element = new SimpleXMLElement('<branch></branch>');
			$element->addChild('version', $this->version_string);
			
			if ($this->eol !== null) {
				$element->addChild('eol', $this->eol->format('Y-m-d'));
			}
			
			if ($this->eol_security !== null) {
				$element->addChild('eol_security', $this->eol_security->format('Y-m-d'));
			}
			
			file_put_contents(
				self::VersionToPath($this->version_string),
				XmlHelpers::SimpleXmlToFormatted($element)
			);
		}
	}