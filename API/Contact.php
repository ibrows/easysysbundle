<?php
namespace Ibrows\EasySysBundle\API;
use Ibrows\EasySysBundle\Connection\Connection;

/**
 * @author marcsteiner
 *
 */
class Contact extends AbstractType
{

    protected $typeIdPrivate = 2;
    protected $typeIdCompany = 1;
    protected $groupId = array(
            151
    );
    protected $countryId = 1;
    protected $description = 'Kontaktperson';

    const IDENTIFY_PRECISION_NONE = 0;
    const IDENTIFY_PRECISION_MINIMUM = 1;
    const IDENTIFY_PRECISION_ALL = 31;

    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->type = 'contact';
    }

    public function searchForExistingPerson($mail, $firstname = null, $name = null, $zip = null, $city = null)
    {
        $simplecrits = array(
                'name_1' => $name,
                'name_2' => $firstname,
                'mail' => $mail,
                'postcode' => $zip,
                'city' => $city,
        );

        return $this->find($simplecrits);
    }

    public function searchForExistingCompany($plz, $city, $company)
    {
        $simplecrits = array(
                'name_1' => $company,
                'postcode' => $plz,
                'city' => $city
        );
        return $this->find($simplecrits);

    }

    public function createCompany($name, $address, $postcode, $city)
    {

        return $this->createContact($name, null, null, null, $address, $postcode, $city, $this->typeIdCompany);
    }

    public function createPerson($name, $firstname, $mail, $phone_fixed, $address, $postcode, $city)
    {
        return $this->createContact($name, $firstname, $mail, $phone_fixed, $address, $postcode, $city, $this->typeIdPrivate);
    }

    protected function createContact($name_1, $name_2, $mail, $phone_fixed, $address, $postcode, $city, $contact_type_id)
    {
        //Save Person

        $myAry = compact(array_keys(get_defined_vars()));
        $myAry['user_id'] = $this->connection->getUserId();
        $myAry['owner_id'] = $this->connection->getUserId();
        $myAry['country_id'] = $this->countryId;
        $myAry['contact_group_ids'] = $this->groupId;
        return $this->connection->call('contact', array(), $myAry, "POST");
    }

    protected function searchForRelation($contact_id, $contact_sub_id)
    {
        $simplecrits = compact(array_keys(get_defined_vars()));
        return $this->connection->call('contact_relation/search', array(), $this->convertSimpleCriterias($simplecrits), "POST");
    }

    protected function createContactRelation($contact_id, $contact_sub_id, $description = null)
    {
        if ($description) {
            $description = $this->description;
        }
        $data = compact(array_keys(get_defined_vars()));
        return $this->connection->call('contact_relation', array(), $data, 'POST');
    }

    protected function addContactWithCompany($name, $firstname, $mail, $zip, $city, $address, $phone, $company, $identify_precision = self::IDENTIFY_PRECISION_MINIMUM)
    {
        $this->output->writeln("try to add company <comment>$company</comment>");
        $contactcompany = $this->searchForExistingCompany($zip, $city, $company);
        $contactcompany = array();
        if ($identify_precision == self::IDENTIFY_PRECISION_MINIMUM) {
            $contactcompany = $this->searchForExistingCompany(null, null, $company);
        } elseif ($identify_precision == self::IDENTIFY_PRECISION_ALL) {
            $contactcompany = $this->searchForExistingCompany($zip, $city, $company);
        }
        if (sizeof($contactcompany) > 0 && isset($contactcompany[0]['id'])) {
            $this->output->writeln("found company <comment>$zip, $city, $company</comment>");
            $contactcompany = $contactcompany[0];
        } else {
            $this->output->writeln("create new company <comment>$zip, $city, $company</comment>");
            $contactcompany = $this->createCompany($company, $address, $zip, $city);
        }
        $companyid = $contactcompany['id'];
        $this->output->writeln("try to add person <comment>$mail</comment>");
        $person = array();
        if ($identify_precision == self::IDENTIFY_PRECISION_MINIMUM) {
            $person = $this->searchForExistingPerson($mail);
        } elseif ($identify_precision == self::IDENTIFY_PRECISION_ALL) {
            $person = $this->searchForExistingPerson($mail, $firstname, $name, $zip, $city);
        }
        if (sizeof($person) > 0 && isset($person[0]['id'])) {
            $this->output->writeln("found person <comment>$mail</comment>  <info>". $person[0]['id']."</info>");
            $person = $person[0];

        } else {
            $this->output->writeln("create new person <comment>$mail, $name, $firstname</comment>");
            $person = $this->createPerson($name, $firstname, $mail, $phone, $address, $zip, $city);
        }
        $personid = $person['id'];
        //Check if there is already a relation between the two contacts
        $relation = $this->searchForRelation($companyid, $personid);
        if (!count($relation)) {
            $this->output->writeln("save new relation <comment>$companyid, $personid</comment>");
            $this->createContactRelation($companyid, $personid);
        } else {
            $this->output->writeln("relation allerady exists<comment>$companyid, $personid</comment>");
        }
        $person['company'] = $companyid;
        return $person;
    }

    protected function addContactWithoutCompany($name, $firstname, $mail, $zip, $city, $address, $phone, $identify_precision = self::IDENTIFY_PRECISION_MINIMUM)
    {
        $this->output->writeln("try to add person <comment>$name, $firstname</comment>");
        $person = array();
        if ($identify_precision == self::IDENTIFY_PRECISION_MINIMUM) {
            $person = $this->searchForExistingPerson($mail);
        } elseif ($identify_precision == self::IDENTIFY_PRECISION_ALL) {
            $person = $this->searchForExistingPerson($mail, $firstname, $name, $zip, $city);
        }
        if (sizeof($person) > 0 && isset($person[0]['id'])) {
            $this->output->writeln("found person <comment>$name, $firstname</comment>");
            $person = $person[0];

        } else {
            $this->output->writeln("create new person <comment>$name, $firstname</comment>");
            $person = $this->createPerson($name, $firstname, $mail, $phone, $address, $zip, $city);
        }
        return $person;
    }

    public function addContact($name, $firstname, $mail, $zip, $city, $address, $phone = null, $company = null, $identify_precision = self::IDENTIFY_PRECISION_MINIMUM)
    {
        $this->output->writeln("try to add contact <comment>$name</comment>");
        $id = null;
        if ($company != null) {
            return $this->addContactWithCompany($name, $firstname, $mail, $zip, $city, $address, $phone, $company, $identify_precision);
        } else {
            return $this->addContactWithoutCompany($name, $firstname, $mail, $zip, $city, $address, $phone, $identify_precision);
        }
    }

    public function save()
    {
        return call_user_method_array('addContact', $this, func_get_args());
    }

    public function create($vars, $type = null)
    {
        $vars['owner_id'] = $this->connection->getUserId();
        return parent::create($vars, $type);
    }
    /**
     * @return int
     */
    public function getTypeIdPrivate()
    {
        return $this->typeIdPrivate;
    }

    /**
     * @param int $typeIdPrivate
     */
    public function setTypeIdPrivate($typeIdPrivate)
    {
        $this->typeIdPrivate = $typeIdPrivate;
        return $this;
    }

    /**
     * @return int
     */
    public function getTypeIdCompany()
    {
        return $this->typeIdCompany;
    }

    /**
     * @param int $typeIdCompany
     */
    public function setTypeIdCompany($typeIdCompany)
    {
        $this->typeIdCompany = $typeIdCompany;
        return $this;
    }

    /**
     * @return array
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId($groupId)
    {
        if (is_array($groupId)) {
            $this->groupId = $groupId;
        } else {
            $this->groupId = array(
                    $groupId
            );
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * @param int $countryId
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;
        return $this;
    }

    /**
     * @return str
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param str $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
}
