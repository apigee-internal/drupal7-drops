<?php

namespace Apigee\Mint;

use Apigee\Exceptions\ParameterException;
use Apigee\Util\Log;
use Apigee\Util\Debugger;
use Apigee\Mint\Base\BaseObject as BaseObject;
use Apigee\Mint\DataStructures\MintCriteria as MintCriteria;

class ReportDefinition extends BaseObject {

  private $mint_criteria;

  private $description;

  private $developer;

  protected $id;

  private $name;

  private $organization;

  private $type;

  public function __construct(\Apigee\Util\APIClient $client) {
    $this->init($client);
    $this->base_url = '/mint/organizations/' . rawurlencode($this->client->getOrg()) . '/report-definitions';
    $this->wrapper_tag = 'reportDefinition';
    $this->developer = $developer;
    $this->id_field = 'id';
    $this->id_is_autogenerated = TRUE;
  }

  public function initValues() {
    $mint_criteria = NULL;
    $description = NULL;
    $developer = NULL;
    $id = NULL;
    $name = NULL;
    $organization = NULL;
    $type = NULL;
  }

  public function loadFromRawData($data, $reset = FALSE) {
    if ($reset) {
      $this->initValues();
    }

    $excluded_properties = array('mintCriteria', 'developer', 'organization');

    foreach (array_keys($data) as $property) {
      if (in_array($property, $excluded_properties)) {
        continue;
      }

      // form the setter method name to invoke setXxxx
      $setter_method = 'set' . ucfirst($property);
      if (method_exists($this, $setter_method)) {
        $this->$setter_method($data[$property]);
      }
      else {
        Log::write(__CLASS__, Log::LOGLEVEL_NOTICE, 'No setter method was found for property "' . $property . '"');
      }
    }

    // Set objects

    if (isset($data['developer'])) {
      $dev = new Developer($this->client);
      $dev->loadFromRawData($data['developer']);
      $this->developer = $dev;
    }

    if (isset($data['organization'])) {
      $organization = new Organization($this->client);
      $organization->loadFromRawData($data['organization']);
      $this->organization = $organization;
    }

    if (isset($data['mintCriteria'])) {
      $this->mint_criteria = new MintCriteria($data['mintCriteria']);
    }
  }

  public function instantiateNew() {
    return new ReportDefinition($this->getClient());
  }

  public function __toString() {
    return json_encode($this);
  }

  public function getListByDeveloper($developer_id = NULL) {
    if (isset($developer_id)) {
      $id = $developer_id;
    }
    else if ($this->developer != null && $this->developer->getEmail() != null){
      $id = $this->developer->getEmail();
    }
    else {
      throw new ParameterException("Report definition identifier not specified.");
    }
    $url = '/mint/organizations/' . rawurlencode($this->client->getOrg()) . '/developers/' . rawurlencode($id) . '/report-definitions';
    $accept_type = 'application/json; charset=utf-8';
    $this->client->get($url, $accept_type);
    $data = $this->client->getResponse();
    $revenue_reports = array();
    foreach ($data['reportDefinition'] as $report) {
      $report_definition = new ReportDefinition($this->getClient());
      $report_definition->setDeveloper($this->developer);
      $report_definition->loadFromRawData($report);
      $revenue_reports[] = $report_definition;
    }
    return $revenue_reports;
  }

  /*
  public function load($id) {
    $url = '/mint/organizations/' . rawurlencode($this->client->getOrg()) . '/report-definitions/' . rawurldecode($id);
    $accept_type = 'application/json; charset=utf-8';
    $this->client->get($url, $accept_type);
    $data = $this->client->getResponse();
    $this->loadFromRawData($report);
  }
  */

  public function getMintCriteria() {
    return $this->mint_criteria;
  }
  public function setMintCriteria($mint_criteria) {
    $this->mint_criteria = $mint_criteria;
  }

  public function getDescription() {
    return $this->description;
  }
  public function setDescription($description) {
    $this->description = $description;
  }

  public function getDeveloper() {
    return $this->developer;
  }
  public function setDeveloper($developer) {
    $this->developer = $developer;
  }

  public function getId() {
    return $this->id;
  }
  public function setId($id) {
    $this->id = $id;
  }

  public function getName() {
    return $this->name;
  }
  public function setName($name) {
    $this->name = $name;
  }

  public function getOrganization() {
    return $this->organization;
  }
  public function setOrganization($organization) {
    $this->organization = $organization;
  }

  public function getType() {
    return $this->type;
  }
  public function setType($type) {
    $this->type = $type;
  }
}