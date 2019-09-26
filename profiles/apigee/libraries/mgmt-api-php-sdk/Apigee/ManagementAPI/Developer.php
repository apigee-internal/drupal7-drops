<?php
/**
 * @file
 * Abstracts the Developer object in the Management API and allows clients to
 * manipulate it.
 *
 * @author djohnson
 */

namespace Apigee\ManagementAPI;

use Apigee\Exceptions\ResponseException;
use Apigee\Exceptions\ParameterException;
use Apigee\Exceptions\TooManyAttributesException;
use Apigee\Util\DebugData;
use Apigee\Util\OrgConfig;

/**
 * Abstracts the Developer object in the Management API and allows clients to
 * manipulate it.
 *
 * @author djohnson
 */
class Developer extends Base
{

    /**
     * If paging is enabled, how many developers can be retrieved per query?
     */
    const MAX_ITEMS_PER_PAGE = 1000;

    /**
     * If paging is enabled, we cannot exceed this number of attributes.
     */
    const MAX_ATTRIBUTE_COUNT = 20;

    /**
     * The apps associated with the developer.
     * @var string[]
     */
    protected $apps;
    /**
     * @var string
     * The developer's email, used to unique identify the developer in Edge.
     */
    protected $email;
    /**
     * @var string
     * Read-only alternate unique ID. Useful when querying developer analytics.
     */
    protected $developerId;
    /**
     * @var string
     * The first name of the developer.
     */
    protected $firstName;
    /**
     * The last name of the developer.
     * @var string
     */
    protected $lastName;
    /**
     * @var string
     * The developer's username.
     */
    protected $userName;
    /**
     * @var string
     * The Apigee organization where the developer is regsitered.
     * This property is read-only.
     */
    protected $organizationName;
    /**
     * @var string
     * The developer status: 'active' or 'inactive'.
     */
    protected $status;
    /**
     * @var string[]
     * Name/value pairs used to extend the default profile.
     */
    protected $attributes;
    /**
     * @var int
     * Unix time when the developer was created.
     * This property is read-only.
     */
    protected $createdAt;
    /**
     * @var string
     * Username of the user who created the developer.
     * This property is read-only.
     */
    protected $createdBy;
    /**
     * @var int
     * Unix time when the developer was last modified.
     * This property is read-only.
     */
    protected $modifiedAt;
    /**
     * @var string
     * Username of the user who last modified the developer.
     * This property is read-only.
     */
    protected $modifiedBy;

    /**
     * @var string[]
     * Read-only list of company identifiers of which this developer is a
     * member.
     */
    protected $companies;

    /**
     * @var bool
     * True to require use of paging when querying developers.
     */
    protected $pagingEnabled = false;

    /**
     * @var int
     * Number of developers fetched per page, if paging is enabled.
     */
    protected $pageSize;

    /**
     * Returns the names of apps associated with the developer.
     *
     * @return string[]
     */
    public function getApps()
    {
        return $this->apps;
    }

  /**
   * Returns the email address associated with the developer.
   *
   * @return string
   */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets/clears the Paging flag.
     *
     * @param bool $flag
     */
    public function usePaging($flag = true)
    {
        $this->pagingEnabled = (bool)$flag;
    }

    /**
     * Reports current status of the Paging flag.
     *
     * @return bool
     */
    public function isPagingEnabled()
    {
        return $this->pagingEnabled;
    }

    /**
     * Sets the email address associated with the developer.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Returns the ID associated with the developer.
     *
     * @return string
     */
    public function getDeveloperId()
    {
        return $this->developerId;
    }

    /**
     * Returns the first name associated with the developer.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the first name associated with the developer.
     *
     * @param string $fname
     */
    public function setFirstName($fname)
    {
        $this->firstName = $fname;
    }

    /**
     * Returns the last name associated with the developer.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the last name associated with the developer.
     *
     * @param string $lname
     */
    public function setLastName($lname)
    {
        $this->lastName = $lname;
    }

    /**
     * Returns the username associated with the developer.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Sets the username associated with the developer.
     *
     * @param string $uname
     */
    public function setUserName($uname)
    {
        $this->userName = $uname;
    }

    /**
     * Returns the developer status: 'active' or 'inactive'.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the developer status: 'active' or 'inactive'.
     *
     * @param string|int|bool $status
     */
    public function setStatus($status)
    {
        if ($status === 0 || $status === false) {
            $status = 'inactive';
        } elseif ($status === 1 || $status === true) {
            $status = 'active';
        }
        if ($status != 'active' && $status != 'inactive') {
            $msg = sprintf('Status may be either active or inactive; value “%s” is invalid', $status);
            throw new ParameterException($msg);
        }
        $this->status = $status;
    }

    /**
     * Returns an attribute associated with the developer, or null if the
     * attribute does not exist.
     *
     * @param string $attr
     * @return string|null
     */
    public function getAttribute($attr)
    {
        if (array_key_exists($attr, $this->attributes)) {
            return $this->attributes[$attr];
        }
        return null;
    }

    /**
     * Sets an attribute on the developer.
     *
     * @param string $attr
     * @param string $value
     */
    public function setAttribute($attr, $value)
    {
        // In paging-enabled environments, there is a hard limit of 20 on the
        // number of attributes any entity may have.
        if ($this->pagingEnabled && count($this->attributes) >= self::MAX_ATTRIBUTE_COUNT) {
            $msg = sprintf('This developer already has %u attributes; cannot add any more.', self::MAX_ATTRIBUTE_COUNT);
            throw new TooManyAttributesException($msg);
        }
        $this->attributes[$attr] = (string)$value;
    }

    /**
     * Returns the attributes associated with the developer.
     *
     * @return string[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns the Unix time when the developer was last modified.
     *
     * @return integer
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Returns a list of string identifiers for companies of which this
     * developer is a member.
     *
     * @return string[]
     */
    public function getCompanies()
    {
        return $this->companies;
    }

    /**
     * Returns page size for paged queries.
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Sets page size for paged queries.
     *
     * @param int $size
     * @throws ParameterException
     */
    public function setPageSize($size)
    {
        $size = intval($size);
        if ($size < 2 || $size > self::MAX_ITEMS_PER_PAGE) {
            $msg = sprintf('Invalid value %d for pageSize; must be between 2 and %u', $size, self::MAX_ITEMS_PER_PAGE);
            throw new ParameterException($msg);
        }
        $this->pageSize = $size;
    }

    /**
     * Initializes default values of all member variables.
     *
     * @param OrgConfig $config
     */
    public function __construct(OrgConfig $config)
    {
        $this->init($config, '/o/' . rawurlencode($config->orgName) . '/developers');
        $this->blankValues();
        $this->pageSize = self::MAX_ITEMS_PER_PAGE;
    }

    /**
     * Loads a developer from the Management API using $email as the unique key.
     *
     * Note that if using a paging-enabled org, a maximum of 100 apps will be
     * returned for a developer. In order to get a canonical listing of a
     * developer's apps, you should invoke DeveloperApp::getList() or
     * DeveloperApp::getListDetail().
     *
     * @param string $email
     *    This can be either the developer's email address or the unique
     *    developerId.
     */
    public function load($email)
    {
        $this->get(rawurlencode($email));
        $developer = $this->responseObj;
        self::loadFromResponse($this, $developer);
    }

    /**
     * Takes the raw Edge response and populates the member variables of the
     * passed-in Developer object from it.
     *
     * @param Developer $developer
     * @param array $response
     */
    protected static function loadFromResponse(Developer &$developer, array $response)
    {
        $developer->apps = $response['apps'];
        $developer->email = $response['email'];
        $developer->developerId = $response['developerId'];
        $developer->firstName = $response['firstName'];
        $developer->lastName = $response['lastName'];
        $developer->userName = $response['userName'];
        $developer->organizationName = $response['organizationName'];
        $developer->status = $response['status'];
        $developer->attributes = array();
        if (array_key_exists('attributes', $response) && is_array($response['attributes'])) {
            foreach ($response['attributes'] as $attribute) {
                $developer->attributes[$attribute['name']] = @$attribute['value'];
            }
        }
        $developer->createdAt = $response['createdAt'];
        $developer->createdBy = $response['createdBy'];
        $developer->modifiedAt = $response['lastModifiedAt'];
        $developer->modifiedBy = $response['lastModifiedBy'];
        if (array_key_exists('companies', $response)) {
            $developer->companies = $response['companies'];
        } else {
            $developer->companies = array();
        }
    }

    /**
     * Attempts to load developer from Management API. Returns true if load was
     * successful.
     *
     * If $email is not supplied, the result will always be false.
     *
     * The $email parameter may either be the actual developer email, or it can
     * be a developerId.
     *
     * @param null|string $email
     * @return bool
     */
    public function validate($email = null)
    {
        if (!empty($email)) {
            try {
                $this->get(rawurlencode($email));
                return true;
            } catch (ResponseException $e) {
            }
        }
        return false;
    }

    /**
     * Saves user data to the Management API. This operates as both insert and
     * update.
     *
     * If user's email doesn't look valid (must contain @), a
     * ParameterException is thrown.
     *
     * @param bool|null $forceUpdate
     *   If false, assume that this is a new instance.
     *   If true, assume that this is an update to an existing instance.
     *   If null, try an update, and if that fails, try an insert.
     * @param string|null $oldEmail
     *   If the developer's email has changed, this field must contain the
     *   previous email value.
     *
     * @throws \Apigee\Exceptions\ParameterException
     */
    public function save($forceUpdate = false, $oldEmail = null)
    {
        // See if we need to brute-force this.
        if ($forceUpdate === null) {
            try {
                $this->save(true, $oldEmail);
            } catch (ResponseException $e) {
                if ($e->getCode() == 404) {
                    // Update failed because dev doesn't exist.
                    // Try insert instead.
                    $this->save(false, $oldEmail);
                } else {
                    // Some other response error.
                    throw $e;
                }
            }
            return;
        }

        if (!$this->validateUser()) {
            $message = 'Developer requires valid-looking email address, firstName, lastName and userName.';
            throw new ParameterException($message);
        }

        if (empty($oldEmail)) {
            $oldEmail = $this->email;
        }

        $payload = array(
            'email' => $this->email,
            'userName' => $this->userName,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        );
        if (!empty($this->attributes)) {
            $payload['attributes'] = array();
            $i = 0;
            foreach ($this->attributes as $name => $value) {
                $i++;
                if ($i > self::MAX_ATTRIBUTE_COUNT && $this->pagingEnabled) {
                    // This truncation occurs silently; should we throw an exception?
                    break;
                }
                $payload['attributes'][] = array('name' => $name, 'value' => $value);
            }
        }
        $url = null;
        if ($forceUpdate || $this->createdAt) {
            if ($this->developerId) {
                $payload['developerId'] = $this->developerId;
            }
            $url = rawurlencode($oldEmail);
        }
        // Save our desired status for later.
        $cached_status = $this->status;
        if ($forceUpdate) {
            $this->put($url, $payload);
        } else {
            $this->post($url, $payload);
        }
        self::loadFromResponse($this, $this->responseObj);
        // We must cache the DebugData from the developer-save call so that
        // we can make it available to clients AFTER the "action" call below.
        $responseData = DebugData::toArray();

        // If status has changed, we must directly change it with a separate
        // POST call, because Edge will ignore a 'status' member in the
        // app-save payload.
        // We must also do this when creating a developer ex nihilo in order
        // to set initial status. Otherwise new developer will have default
        // status, which is generally 'approved'.
        $this->post($this->email . '?action=' . $cached_status);
        $this->status = $cached_status;

        // Restore DebugData from cached response.
        DebugData::fromArray($responseData);
    }

    /**
     * Deletes a developer.
     *
     * If $email is not supplied, $this->email is used.
     *
     * @param null|string $email
     */
    public function delete($email = null)
    {
        $email = $email ? : $this->email;
        $this->httpDelete(rawurlencode($email));
        if ($email == $this->email) {
            $this->blankValues();
        }
    }

    /**
     * Returns an array of all developer emails for this org.
     *
     * @return string[]
     */
    public function listDevelopers()
    {
        $developers = array();
        if ($this->pagingEnabled) {
            // Scroll through pages, saving the last key on each page.
            // Each subsequent request asks for a page of results starting
            // with the last key from the previous page.
            $lastKey = null;
            while (true) {
                $queryString = '?count=' . $this->pageSize;
                if (isset($lastKey)) {
                    $queryString .= '&startKey=' . urlencode($lastKey);
                }
                $this->get($queryString);
                $developerSubset = $this->responseObj;
                $subsetCount = count($developerSubset);
                if ($subsetCount == 0) {
                    break;
                }
                if (isset($lastKey)) {
                    // Avoid duplicating the last key, which is the first key
                    // on this page.
                    array_shift($developerSubset);
                }
                $developers = array_merge($developers, $developerSubset);
                if ($subsetCount == $this->pageSize) {
                    $lastKey = end($developerSubset);
                } else {
                    break;
                }
            }
        } else {
            $this->get();
            $developers = $this->responseObj;
        }
        return $developers;
    }

    /**
     * Returns an array of all developers in the org.
     *
     * Note that if using a paging-enabled org, a maximum of 100 apps will be
     * listed for each developer that comes back in this list. In order to get
     * a canonical listing of a developer's apps, you should invoke
     * DeveloperApp::getList() or DeveloperApp::getListDetail().
     *
     * @return Developer[]
     */
    public function loadAllDevelopers()
    {
        $developers = array();
        if ($this->pagingEnabled) {
            $lastKey = null;
            while (true) {
                $queryString = '?expand=true&count=' . $this->pageSize;
                if (isset($lastKey)) {
                    $queryString .= '&startKey=' . urlencode($lastKey);
                }
                $this->get($queryString);
                $developerSubset = $this->responseObj;
                $subsetCount = count($developerSubset['developer']);
                if ($subsetCount == 0) {
                    break;
                }
                if (isset($lastKey)) {
                    // Avoid duplicating the last key, which is the first key
                    // on this page.
                    array_shift($developerSubset['developer']);
                }
                foreach ($developerSubset['developer'] as $dev) {
                    $developer = new Developer($this->config);
                    self::loadFromResponse($developer, $dev);
                    $developers[] = $developer;
                }
                if ($subsetCount == $this->pageSize) {
                    $lastDeveloper = end($developerSubset['developer']);
                    $lastKey = $lastDeveloper['developerId'];
                } else {
                    break;
                }
            }
        } else {
            $this->get('?expand=true');
            $developerList = $this->responseObj;
            foreach ($developerList['developer'] as $dev) {
                $developer = new Developer($this->config);
                self::loadFromResponse($developer, $dev);
                $developers[] = $developer;
            }
        }
        return $developers;
    }

    /**
     * Ensures that current developer's email is valid.
     *
     * If first name and/or last name are not supplied, they are auto-
     * populated based on email.
     *
     * @return bool
     */
    public function validateUser()
    {
        if (!empty($this->email) && (strpos($this->email, '@') > 0)) {
            $name = explode('@', $this->email, 2);
            if (empty($this->firstName)) {
                $this->firstName = $name[0];
            }
            if (empty($this->lastName)) {
                $this->lastName = $name[1];
            }
        }
        return (
            !empty($this->firstName)
            && !empty($this->lastName)
            && !empty($this->userName)
            && !empty($this->email)
            && strpos($this->email, '@') > 0
        );
    }

    /**
     * Resets this object's properties to null or to empty arrays based on type.
     */
    public function blankValues()
    {
        $this->apps = array();
        $this->email = null;
        $this->developerId = null;
        $this->firstName = null;
        $this->lastName = null;
        $this->userName = null;
        $this->organizationName = null;
        $this->status = null;
        $this->attributes = array();
        $this->createdAt = null;
        $this->createdBy = null;
        $this->modifiedAt = null;
        $this->modifiedBy = null;
        $this->companies = array();
    }

    /**
     * Converts this object's properties into an array for external use.
     *
     * @return array
     */
    public function toArray($include_debug_data = true)
    {
        $properties = array_keys(get_object_vars($this));
        $excluded_properties = array_keys(get_class_vars(get_parent_class($this)));
        $excluded_properties[] = 'pagingEnabled';
        $excluded_properties[] = 'pageSize';
        $output = array();
        foreach ($properties as $property) {
            if (!in_array($property, $excluded_properties)) {
                $output[$property] = $this->$property;
            }
        }
        $output['debugData'] = $include_debug_data ? $this->getDebugData() : null;
        return $output;
    }

    /**
     * Populates this object based on an incoming array generated by the
     * toArray() method above.
     *
     * @param $array
     */
    public function fromArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (property_exists($this, $key) && $key != 'debugData') {
                // Explicitly cast all attribute values to string.
                if ($key == 'attributes' && is_array($array['attributes'])) {
                    $attribute_names = array_keys($array['attributes']);
                    foreach ($attribute_names as $attribute_name) {
                        $array['attributes'][$attribute_name] = (string)$array['attributes'][$attribute_name];
                    }
                }
                $this->{$key} = $value;
            }
        }
    }
}
