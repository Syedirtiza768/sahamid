<?php

namespace SAHamid\BI\Domain;

class MetricDefinition
{
	private $id;
	private $version;
	private $name;
	private $description;
	private $status;
	private $handler;
	private $grain;
	private $dateRole;
	private $formula;
	private $lineage;
	private $dimensions;
	private $permission;
	private $owner;
	private $caveats;

	public function __construct($id, $version, $name, $description, $status, $handler, $grain, $dateRole, $formula, array $lineage, array $dimensions, $permission, $owner, array $caveats = array())
	{
		$this->id = (string) $id;
		$this->version = (int) $version;
		$this->name = (string) $name;
		$this->description = (string) $description;
		$this->status = (string) $status;
		$this->handler = $handler === null ? null : (string) $handler;
		$this->grain = (string) $grain;
		$this->dateRole = $dateRole === null ? null : (string) $dateRole;
		$this->formula = (string) $formula;
		$this->lineage = $lineage;
		$this->dimensions = $dimensions;
		$this->permission = (string) $permission;
		$this->owner = (string) $owner;
		$this->caveats = $caveats;
	}

	public function getId() { return $this->id; }
	public function getVersion() { return $this->version; }
	public function getName() { return $this->name; }
	public function getDescription() { return $this->description; }
	public function getStatus() { return $this->status; }
	public function getHandler() { return $this->handler; }
	public function getGrain() { return $this->grain; }
	public function getDateRole() { return $this->dateRole; }
	public function getFormula() { return $this->formula; }
	public function getLineage() { return $this->lineage; }
	public function getDimensions() { return $this->dimensions; }
	public function getPermission() { return $this->permission; }
	public function getOwner() { return $this->owner; }
	public function getCaveats() { return $this->caveats; }

	public function isExecutable()
	{
		return $this->status === 'trusted' && $this->handler !== null;
	}

	public function toArray()
	{
		return array(
			'id' => $this->id,
			'version' => $this->version,
			'name' => $this->name,
			'description' => $this->description,
			'status' => $this->status,
			'grain' => $this->grain,
			'date_role' => $this->dateRole,
			'formula' => $this->formula,
			'lineage' => $this->lineage,
			'dimensions' => $this->dimensions,
			'permission' => $this->permission,
			'owner' => $this->owner,
			'caveats' => $this->caveats,
		);
	}
}
