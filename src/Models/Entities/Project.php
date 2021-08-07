<?php

namespace App\Models\Entities;

use App\Utils\ServerLogger;

class Project {

  /* Define attributes */
  public int $id;
  public string $project_name;
  public string $subtype;
  public string $current_status;
  public ?float $capacity_mw;
  public ?int $year_of_completion;
  public ?string $country_list_of_sponsor_developer;
  public ?string $sponsor_developer_company;
  public ?string $country_list_of_lender_financier;
  public ?string $lender_financier_company;
  public ?string $country_list_of_construction_epc;
  public ?string $construction_company_epc_participant;
  public string $country;
  public ?string $province_state;
  public string $district;
  public ?string $tributary;
  public float $latitude;
  public float $longitude;
  public ?string $proximity;
  public ?float $avg_annual_output_mwh;
  public ?string $data_source;
  public ?string $announce_more_information;
  public ?string $link;
  public ?string $latest_update;


  /**
   * Constructor to initialize Project
   * @return void
   */
  public function __construct(
    int $id,
    string $project_name,
    string $subtype,
    string $current_status,
    ?float $capacity_mw,
    ?int $year_of_completion,
    ?string $country_list_of_sponsor_developer,
    ?string $sponsor_developer_company,
    ?string $country_list_of_lender_financier,
    ?string $lender_financier_company,
    ?string $country_list_of_construction_epc,
    ?string $construction_company_epc_participant,
    string $country,
    ?string $province_state,
    string $district,
    ?string $tributary,
    float $latitude,
    float $longitude,
    ?string $proximity,
    ?float $avg_annual_output_mwh,
    ?string $data_source,
    ?string $announce_more_information,
    ?string $link,
    ?string $latest_update
  ) {
    // If Don't have NULL coalesce -> Required field!
    $this->id = $id; // Required!
    $this->project_name = $project_name; // Required!
    $this->subtype = $subtype; // Required!
    $this->current_status = $current_status; // Required!
    $this->capacity_mw = $capacity_mw;
    $this->year_of_completion = $year_of_completion;
    $this->country_list_of_sponsor_developer = $country_list_of_sponsor_developer == "" ? NULL : $country_list_of_sponsor_developer;
    $this->sponsor_developer_company =  $sponsor_developer_company == "" ? NULL : $sponsor_developer_company;
    $this->country_list_of_lender_financier = $country_list_of_lender_financier == "" ? NULL : $country_list_of_lender_financier;
    $this->lender_financier_company = $lender_financier_company == "" ? NULL : $lender_financier_company;
    $this->country_list_of_construction_epc = $country_list_of_construction_epc == "" ? NULL : $country_list_of_construction_epc;
    $this->construction_company_epc_participant = $construction_company_epc_participant == "" ? NULL : $country_list_of_construction_epc;
    $this->country = $country; // Required!
    $this->province_state = $province_state; // Required!
    $this->district = $district; // Required!
    $this->tributary = $tributary == "" ? NULL : $tributary;
    $this->latitude = $latitude; // Required!
    $this->longitude = $longitude; // Required!
    $this->proximity = $proximity == "" ? NULL : $proximity;
    $this->avg_annual_output_mwh = $avg_annual_output_mwh;
    $this->data_source = $data_source == "" ? NULL : $data_source;
    $this->announce_more_information = $announce_more_information == "" ? NULL : $announce_more_information;
    $this->link = $link == "" ? NULL : $link;
    $this->latest_update = $latest_update == "" ? NULL : $latest_update;
  }

  /**
   * Get the value of id
   *
   * @return int
   */
  public function getId(): int {
    return $this->id;
  }

  /**
   * Set the value of id
   *
   * @param int $id
   *
   * @return self
   */
  public function setId(int $id): self {
    $this->id = $id;

    return $this;
  }

  /**
   * Get the value of project_name
   *
   * @return string
   */
  public function getProjectName(): string {
    return $this->project_name;
  }

  /**
   * Set the value of project_name
   *
   * @param string $project_name
   *
   * @return self
   */
  public function setProjectName(string $project_name): self {
    $this->project_name = $project_name;

    return $this;
  }

  /**
   * Get the value of subtype
   *
   * @return string
   */
  public function getSubtype(): string {
    return $this->subtype;
  }

  /**
   * Set the value of subtype
   *
   * @param string $subtype
   *
   * @return self
   */
  public function setSubtype(string $subtype): self {
    $this->subtype = $subtype;

    return $this;
  }

  /**
   * Get the value of current_status
   *
   * @return string
   */
  public function getCurrentStatus(): string {
    return $this->current_status;
  }

  /**
   * Set the value of current_status
   *
   * @param string $current_status
   *
   * @return self
   */
  public function setCurrentStatus(string $current_status): self {
    $this->current_status = $current_status;

    return $this;
  }

  /**
   * Get the value of capacity_mw
   *
   * @return float
   */
  public function getCapacityMw(): float {
    return $this->capacity_mw;
  }

  /**
   * Set the value of capacity_mw
   *
   * @param float $capacity_mw
   *
   * @return self
   */
  public function setCapacityMw(float $capacity_mw): self {
    $this->capacity_mw = $capacity_mw;

    return $this;
  }

  /**
   * Get the value of year_of_completion
   *
   * @return int
   */
  public function getYearOfCompletion(): int {
    return $this->year_of_completion;
  }

  /**
   * Set the value of year_of_completion
   *
   * @param int $year_of_completion
   *
   * @return self
   */
  public function setYearOfCompletion(int $year_of_completion): self {
    $this->year_of_completion = $year_of_completion;

    return $this;
  }

  /**
   * Get the value of country_list_of_sponsor_developer
   *
   * @return string
   */
  public function getCountryListOfSponsorDeveloper(): string {
    return $this->country_list_of_sponsor_developer;
  }

  /**
   * Set the value of country_list_of_sponsor_developer
   *
   * @param string $country_list_of_sponsor_developer
   *
   * @return self
   */
  public function setCountryListOfSponsorDeveloper(string $country_list_of_sponsor_developer): self {
    $this->country_list_of_sponsor_developer = $country_list_of_sponsor_developer;

    return $this;
  }

  /**
   * Get the value of sponsor_developer_company
   *
   * @return string
   */
  public function getSponsorDeveloperCompany(): string {
    return $this->sponsor_developer_company;
  }

  /**
   * Set the value of sponsor_developer_company
   *
   * @param string $sponsor_developer_company
   *
   * @return self
   */
  public function setSponsorDeveloperCompany(string $sponsor_developer_company): self {
    $this->sponsor_developer_company = $sponsor_developer_company;

    return $this;
  }

  /**
   * Get the value of country_list_of_lender_financier
   *
   * @return string
   */
  public function getCountryListOfLenderFinancier(): string {
    return $this->country_list_of_lender_financier;
  }

  /**
   * Set the value of country_list_of_lender_financier
   *
   * @param string $country_list_of_lender_financier
   *
   * @return self
   */
  public function setCountryListOfLenderFinancier(string $country_list_of_lender_financier): self {
    $this->country_list_of_lender_financier = $country_list_of_lender_financier;

    return $this;
  }

  /**
   * Get the value of lender_financier_company
   *
   * @return string
   */
  public function getLenderFinancierCompany(): string {
    return $this->lender_financier_company;
  }

  /**
   * Set the value of lender_financier_company
   *
   * @param string $lender_financier_company
   *
   * @return self
   */
  public function setLenderFinancierCompany(string $lender_financier_company): self {
    $this->lender_financier_company = $lender_financier_company;

    return $this;
  }

  /**
   * Get the value of country_list_of_construction_epc
   *
   * @return string
   */
  public function getCountryListOfConstructionEpc(): string {
    return $this->country_list_of_construction_epc;
  }

  /**
   * Set the value of country_list_of_construction_epc
   *
   * @param string $country_list_of_construction_epc
   *
   * @return self
   */
  public function setCountryListOfConstructionEpc(string $country_list_of_construction_epc): self {
    $this->country_list_of_construction_epc = $country_list_of_construction_epc;

    return $this;
  }

  /**
   * Get the value of construction_company_epc_participant
   *
   * @return string
   */
  public function getConstructionCompanyEpcParticipant(): string {
    return $this->construction_company_epc_participant;
  }

  /**
   * Set the value of construction_company_epc_participant
   *
   * @param string $construction_company_epc_participant
   *
   * @return self
   */
  public function setConstructionCompanyEpcParticipant(string $construction_company_epc_participant): self {
    $this->construction_company_epc_participant = $construction_company_epc_participant;

    return $this;
  }

  /**
   * Get the value of country
   *
   * @return string
   */
  public function getCountry(): string {
    return $this->country;
  }

  /**
   * Set the value of country
   *
   * @param string $country
   *
   * @return self
   */
  public function setCountry(string $country): self {
    $this->country = $country;

    return $this;
  }

  /**
   * Get the value of province_state
   *
   * @return string
   */
  public function getProvinceState(): string {
    return $this->province_state;
  }

  /**
   * Set the value of province_state
   *
   * @param string $province_state
   *
   * @return self
   */
  public function setProvinceState(string $province_state): self {
    $this->province_state = $province_state;

    return $this;
  }

  /**
   * Get the value of district
   *
   * @return string
   */
  public function getDistrict(): string {
    return $this->district;
  }

  /**
   * Set the value of district
   *
   * @param string $district
   *
   * @return self
   */
  public function setDistrict(string $district): self {
    $this->district = $district;

    return $this;
  }

  /**
   * Get the value of tributary
   *
   * @return string
   */
  public function getTributary(): string {
    return $this->tributary;
  }

  /**
   * Set the value of tributary
   *
   * @param string $tributary
   *
   * @return self
   */
  public function setTributary(string $tributary): self {
    $this->tributary = $tributary;

    return $this;
  }

  /**
   * Get the value of latitude
   *
   * @return float
   */
  public function getLatitude(): float {
    return $this->latitude;
  }

  /**
   * Set the value of latitude
   *
   * @param float $latitude
   *
   * @return self
   */
  public function setLatitude(float $latitude): self {
    $this->latitude = $latitude;

    return $this;
  }

  /**
   * Get the value of longitude
   *
   * @return float
   */
  public function getLongitude(): float {
    return $this->longitude;
  }

  /**
   * Set the value of longitude
   *
   * @param float $longitude
   *
   * @return self
   */
  public function setLongitude(float $longitude): self {
    $this->longitude = $longitude;

    return $this;
  }

  /**
   * Get the value of proximity
   *
   * @return string
   */
  public function getProximity(): string {
    return $this->proximity;
  }

  /**
   * Set the value of proximity
   *
   * @param string $proximity
   *
   * @return self
   */
  public function setProximity(string $proximity): self {
    $this->proximity = $proximity;

    return $this;
  }

  /**
   * Get the value of avg_annual_output_mwh
   *
   * @return float
   */
  public function getAvgAnnualOutputMwh(): float {
    return $this->avg_annual_output_mwh;
  }

  /**
   * Set the value of avg_annual_output_mwh
   *
   * @param float $avg_annual_output_mwh
   *
   * @return self
   */
  public function setAvgAnnualOutputMwh(float $avg_annual_output_mwh): self {
    $this->avg_annual_output_mwh = $avg_annual_output_mwh;

    return $this;
  }

  /**
   * Get the value of data_source
   *
   * @return string
   */
  public function getDataSource(): string {
    return $this->data_source;
  }

  /**
   * Set the value of data_source
   *
   * @param string $data_source
   *
   * @return self
   */
  public function setDataSource(string $data_source): self {
    $this->data_source = $data_source;

    return $this;
  }

  /**
   * Get the value of announce_more_information
   *
   * @return string
   */
  public function getAnnounceMoreInformation(): string {
    return $this->announce_more_information;
  }

  /**
   * Set the value of announce_more_information
   *
   * @param string $announce_more_information
   *
   * @return self
   */
  public function setAnnounceMoreInformation(string $announce_more_information): self {
    $this->announce_more_information = $announce_more_information;

    return $this;
  }

  /**
   * Get the value of link
   *
   * @return string
   */
  public function getLink(): string {
    return $this->link;
  }

  /**
   * Set the value of link
   *
   * @param string $link
   *
   * @return self
   */
  public function setLink(string $link): self {
    $this->link = $link;

    return $this;
  }

  /**
   * Get the value of latest_update
   *
   * @return string
   */
  public function getLatestUpdate(): string {
    return $this->latest_update;
  }

  /**
   * Set the value of latest_update
   *
   * @param string $latest_update
   *
   * @return self
   */
  public function setLatestUpdate(string $latest_update): self {
    $this->latest_update = $latest_update;

    return $this;
  }
}
