<?php

/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

use UnitedPrototype\GoogleAnalytics\Internals\Util;

use DateTime;

/**
 * You should serialize this object and store it in e.g. the user database to keep it
 * persistent for the same user permanently (similar to the "__umtz" cookie of
 * the GA Javascript client).
 */
class Campaign {
	
	/**
	 * See self::TYPE_* constants, will be mapped to "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var string
	 */
	protected $type;
	
	/**
	 * Time of the creation of this campaign, will be mapped to "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var DateTime
	 */
	protected $creationTime;
	
	/**
	 * Response Count, will be mapped to "__utmz" parameter.
	 * 
	 * Is also used to determine whether the campaign is new or repeated,
	 * which will be mapped to "utmcn" and "utmcr" parameters.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @see Internals\ParameterHolder::$utmcn
	 * @see Internals\ParameterHolder::$utmcr
	 * @var int
	 */
	protected $responseCount = 0;
	
	/**
	 * Campaign ID, a.k.a. "utm_id" query parameter for ga.js
	 * Will be mapped to "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var int
	 */
	protected $id;
	
	/**
	 * Source, a.k.a. "utm_source" query parameter for ga.js.
	 * Will be mapped to "utmcsr" key in "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var string
	 */
	protected $source;
	
	/**
	 * Google AdWords Click ID, a.k.a. "gclid" query parameter for ga.js.
	 * Will be mapped to "utmgclid" key in "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var string
	 */
	protected $gClickId;
	
	/**
	 * DoubleClick (?) Click ID. Will be mapped to "utmdclid" key in "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var string
	 */
	protected $dClickId;
	
	/**
	 * Name, a.k.a. "utm_campaign" query parameter for ga.js.
	 * Will be mapped to "utmccn" key in "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var string
	 */
	protected $name;
	
	/**
	 * Medium, a.k.a. "utm_medium" query parameter for ga.js.
	 * Will be mapped to "utmcmd" key in "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var string
	 */
	protected $medium;
	
	/**
	 * Terms/Keywords, a.k.a. "utm_term" query parameter for ga.js.
	 * Will be mapped to "utmctr" key in "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var string
	 */
	protected $term;
	
	/**
	 * Ad Content Description, a.k.a. "utm_content" query parameter for ga.js.
	 * Will be mapped to "utmcct" key in "__utmz" parameter.
	 * 
	 * @see Internals\ParameterHolder::$__utmz
	 * @var string
	 */
	protected $content;
	
	
	/**
	 * @const string
	 */
	const TYPE_DIRECT = 'direct';
	/**
	 * @const string
	 */
	const TYPE_ORGANIC = 'organic';
	/**
	 * @const string
	 */
	const TYPE_REFERRAL = 'referral';
	
	
	/**
	 * @see createFromReferrer
	 * @param string $type See TYPE_* constants
	 */
	public function __construct($type) {
		if(!in_array($type, array(self::TYPE_DIRECT, self::TYPE_ORGANIC, self::TYPE_REFERRAL))) {
			Tracker::_raiseError('Campaign type has to be one of the Campaign::TYPE_* constant values.', __METHOD__);
		}
		
		$this->type = $type;
		
		switch($type) {
			// See http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/campaign/CampaignManager.as#375
			case self::TYPE_DIRECT:
				$this->name   = '(direct)';
				$this->source = '(direct)';
				$this->medium = '(none)';
				break;
			// See http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/campaign/CampaignManager.as#340
			case self::TYPE_REFERRAL:
				$this->name   = '(referral)';
				$this->medium = 'referral';
				break;
			// See http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/campaign/CampaignManager.as#280
			case self::TYPE_ORGANIC:
				$this->name   = '(organic)';
				$this->medium = 'organic';
				break;
		}
		
		$this->creationTime = new DateTime();
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/campaign/CampaignManager.as#333
	 * @param string $url
	 * @return \UnitedPrototype\GoogleAnalytics\Campaign
	 */
	public static function createFromReferrer($url) {
		$instance = new static(self::TYPE_REFERRAL);
		$urlInfo = parse_url($url);
		$instance->source  = $urlInfo['host'];
		$instance->content = $urlInfo['path'];
		
		return $instance;
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/campaign/CampaignTracker.as#153
	 */
	public function validate() {
		// NOTE: gaforflash states that id and gClickId must also be specified,
		// but that doesn't seem to be correct
		if(!$this->source) {
			Tracker::_raiseError('Campaigns need to have at least the "source" attribute defined.', __METHOD__);
		}
	}
	
	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
	
	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @param DateTime $creationTime
	 */
	public function setCreationTime(DateTime $creationTime) {
		$this->creationTime = $creationTime;
	}
	
	/**
	 * @return DateTime
	 */
	public function getCreationTime() {
		return $this->creationTime;
	}
	
	/**
	 * @param int $esponseCount
	 */
	public function setResponseCount($responseCount) {
		$this->responseCount = (int)$responseCount;
	}
	
	/**
	 * @return int
	 */
	public function getResponseCount() {
		return $this->responseCount;
	}
	
	/**
	 * @param int $byAmount
	 */
	public function increaseResponseCount($byAmount = 1) {
		$this->responseCount += $byAmount;
	}
	
	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	
	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param string $source
	 */
	public function setSource($source) {
		$this->source = $source;
	}
	
	/**
	 * @return string
	 */
	public function getSource() {
		return $this->source;
	}
	
	/**
	 * @param string $gClickId
	 */
	public function setGClickId($gClickId) {
		$this->gClickId = $gClickId;
	}
	
	/**
	 * @return string
	 */
	public function getGClickId() {
		return $this->gClickId;
	}
	
	/**
	 * @param string $dClickId
	 */
	public function setDClickId($dClickId) {
		$this->dClickId = $dClickId;
	}
	
	/**
	 * @return string
	 */
	public function getDClickId() {
		return $this->dClickId;
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $medium
	 */
	public function setMedium($medium) {
		$this->medium = $medium;
	}
	
	/**
	 * @return string
	 */
	public function getMedium() {
		return $this->medium;
	}
	
	/**
	 * @param string $term
	 */
	public function setTerm($term) {
		$this->term = $term;
	}
	
	/**
	 * @return string
	 */
	public function getTerm() {
		return $this->term;
	}
	
	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}
	
	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}
	
}

}





/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

/**
 * Note: Doesn't necessarily have to be consistent across requests, as it doesn't
 * alter the actual tracking result.
 * 
 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/GIFRequest.as
 */
class Config {
	
	/**
	 * How strict should errors get handled? After all, we do just do some
	 * tracking stuff here, and errors shouldn't break an application's
	 * functionality in production.
	 * RECOMMENDATION: Exceptions during deveopment, warnings in production.
	 * 
	 * Assign any value of the self::ERROR_SEVERITY_* constants.
	 * 
	 * @see Tracker::_raiseError()
	 * @var int
	 */
	protected $errorSeverity = self::ERROR_SEVERITY_EXCEPTIONS;
	
	/**
	 * Ignore all errors completely.
	 */
	const ERROR_SEVERITY_SILENCE    = 0;
	/**
	 * Trigger PHP errors with a E_USER_WARNING error level.
	 */
	const ERROR_SEVERITY_WARNINGS   = 1;
	/**
	 * Throw UnitedPrototype\GoogleAnalytics\Exception exceptions.
	 */
	const ERROR_SEVERITY_EXCEPTIONS = 2;
	
	/**
	 * Whether to just queue all requests on HttpRequest::fire() and actually send
	 * them on PHP script shutdown after all other tasks are done.
	 * 
	 * This has two advantages:
	 * 1) It effectively doesn't affect app performance
	 * 2) It can e.g. handle custom variables that were set after scheduling a request
	 * 
	 * @see Internals\Request\HttpRequest::fire()
	 * @var bool
	 */
	protected $sendOnShutdown = false;
	
	/**
	 * Whether to make asynchronous requests to GA without waiting for any
	 * response (speeds up doing requests).
	 * 
	 * @see Internals\Request\HttpRequest::send()
	 * @var bool
	 */
	protected $fireAndForget = false;
	
	/**
	 * Logging callback, registered via setLoggingCallback(). Will be fired
	 * whenever a request gets sent out and receives the full HTTP request
	 * as the first and the full HTTP response (or null if the "fireAndForget"
	 * option or simulation mode are used) as the second argument.
	 * 
	 * @var \Closure
	 */
	protected $loggingCallback;
	
	/**
	 * Seconds (float allowed) to wait until timeout when connecting to the
	 * Google analytics endpoint host
	 * 
	 * @see Internals\Request\HttpRequest::send()
	 * @var float
	 */
	protected $requestTimeout = 1;
	
	// FIXME: Add SSL support, https://ssl.google-analytics.com
	
	/**
	 * Google Analytics tracking request endpoint host. Can be set to null to
	 * silently simulate (and log) requests without actually sending them.
	 * 
	 * @see Internals\Request\HttpRequest::send()
	 * @var string
	 */
	protected $endPointHost = 'www.google-analytics.com';
	
	/**
	 * Google Analytics tracking request endpoint path
	 * 
	 * @see Internals\Request\HttpRequest::send()
	 * @var string
	 */
	protected $endPointPath = '/__utm.gif';
	
	/**
	 * Whether to anonymize IP addresses within Google Analytics by stripping
	 * the last IP address block, will be mapped to "aip" parameter
	 * 
	 * @see Internals\ParameterHolder::$aip
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gat.html#_gat._anonymizeIp
	 * @var bool
	 */
	protected $anonymizeIpAddresses = false;
	
	/**
	 * Defines a new sample set size (0-100) for Site Speed data collection.
	 * By default, a fixed 1% sampling of your site visitors make up the data pool from which
	 * the Site Speed metrics are derived.
	 * 
	 * @see Page::$loadTime
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._setSiteSpeedSampleRate
	 * @var int
	 */
	protected $sitespeedSampleRate = 1;
	
	
	/**
	 * @param array $properties
	 */
	public function __construct(array $properties = array()) {
		foreach($properties as $property => $value) {
			// PHP doesn't care about case in method names
			$setterMethod = 'set' . $property;
			
			if(method_exists($this, $setterMethod)) {
				$this->$setterMethod($value);
			} else {
				return Tracker::_raiseError('There is no setting "' . $property . '".', __METHOD__);
			}
		}
	}
	
	/**
	 * @return int See self::ERROR_SEVERITY_* constants
	 */
	public function getErrorSeverity() {
		return $this->errorSeverity;
	}
	
	/**
	 * @param int $errorSeverity See self::ERROR_SEVERITY_* constants
	 */
	public function setErrorSeverity($errorSeverity) {
		$this->errorSeverity = $errorSeverity;
	}
	
	/**
	 * @return bool
	 */
	public function getSendOnShutdown() {
		return $this->sendOnShutdown;
	}
	
	/**
	 * @param bool $sendOnShutdown
	 */
	public function setSendOnShutdown($sendOnShutdown) {
		$this->sendOnShutdown = $sendOnShutdown;
	}
	
	/**
	 * @return bool
	 */
	public function getFireAndForget() {
		return $this->fireAndForget;
	}
	
	/**
	 * @param bool $fireAndForget
	 */
	public function setFireAndForget($fireAndForget) {
		$this->fireAndForget = (bool)$fireAndForget;
	}
	
	/**
	 * @return \Closure|null
	 */
	public function getLoggingCallback() {
		return $this->loggingCallback;
	}
	
	/**
	 * @param \Closure $callback
	 */
	public function setLoggingCallback(\Closure $callback) {
		$this->loggingCallback = $callback;
	}
	
	/**
	 * @return float
	 */
	public function getRequestTimeout() {
		return $this->requestTimeout;
	}
	
	/**
	 * @param float $requestTimeout
	 */
	public function setRequestTimeout($requestTimeout) {
		$this->requestTimeout = (float)$requestTimeout;
	}
	
	/**
	 * @return string|null
	 */
	public function getEndPointHost() {
		return $this->endPointHost;
	}
	
	/**
	 * @param string|null $endPointHost
	 */
	public function setEndPointHost($endPointHost) {
		$this->endPointHost = $endPointHost;
	}
	
	/**
	 * @return string
	 */
	public function getEndPointPath() {
		return $this->endPointPath;
	}
	
	/**
	 * @param string $endPointPath
	 */
	public function setEndPointPath($endPointPath) {
		$this->endPointPath = $endPointPath;
	}
	
	/**
	 * @return bool
	 */
	public function getAnonymizeIpAddresses() {
		return $this->anonymizeIpAddresses;
	}
	
	/**
	 * @param bool $anonymizeIpAddresses
	 */
	public function setAnonymizeIpAddresses($anonymizeIpAddresses) {
		$this->anonymizeIpAddresses = $anonymizeIpAddresses;
	}
	
	/**
	 * @return int
	 */
	public function getSitespeedSampleRate() {
		return $this->sitespeedSampleRate;
	}
	
	/**
	 * @param int $sitespeedSampleRate
	 */
	public function setSitespeedSampleRate($sitespeedSampleRate) {
		if((int)$sitespeedSampleRate != (float)$sitespeedSampleRate || $sitespeedSampleRate < 0 || $sitespeedSampleRate > 100) {
			return Tracker::_raiseError('For consistency with ga.js, sample rates must be specified as a number between 0 and 100.', __METHOD__);
		}
		
		$this->sitespeedSampleRate = (int)$sitespeedSampleRate;
	}

}

}



/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

use UnitedPrototype\GoogleAnalytics\Internals\Util;

/**
 * @link http://code.google.com/apis/analytics/docs/tracking/gaTrackingCustomVariables.html
 */
class CustomVariable {
	
	/**
	 * @var int
	 */
	protected $index;
	
	/**
	 * WATCH OUT: It's a known issue that GA will not decode URL-encoded characters
	 * in custom variable names and values properly, so spaces will show up
	 * as "%20" in the interface etc.
	 * 
	 * @link http://www.google.com/support/forum/p/Google%20Analytics/thread?tid=2cdb3ec0be32e078
	 * @var string
	 */
	protected $name;
	
	/**
	 * WATCH OUT: It's a known issue that GA will not decode URL-encoded characters
	 * in custom variable names and values properly, so spaces will show up
	 * as "%20" in the interface etc.
	 * 
	 * @link http://www.google.com/support/forum/p/Google%20Analytics/thread?tid=2cdb3ec0be32e078
	 * @var mixed
	 */
	protected $value;
	
	/**
	 * See SCOPE_* constants
	 * 
	 * @var int
	 */
	protected $scope = self::SCOPE_PAGE;
	
	
	/**
	 * @const int
	 */
	const SCOPE_VISITOR = 1;
	/**
	 * @const int
	 */
	const SCOPE_SESSION = 2;
	/**
	 * @const int
	 */
	const SCOPE_PAGE    = 3;
	
	
	/**
	 * @param int $index
	 * @param string $name
	 * @param mixed $value
	 * @param int $scope See SCOPE_* constants
	 */
	public function __construct($index = null, $name = null, $value = null, $scope = null) {
		if($index !== null) $this->setIndex($index);
		if($name  !== null) $this->setName($name);
		if($value !== null) $this->setValue($value);
		if($scope !== null) $this->setScope($scope);
	}
	
	public function validate() {
		// According to the GA documentation, there is a limit to the combined size of
		// name and value of 64 bytes after URL encoding,
		// see http://code.google.com/apis/analytics/docs/tracking/gaTrackingCustomVariables.html#varTypes
		// and http://xahlee.org/js/google_analytics_tracker_2010-07-01_expanded.js line 563
		// This limit was increased to 128 bytes BEFORE encoding with the 2012-01 release of ga.js however,
		// see http://code.google.com/apis/analytics/community/gajs_changelog.html
		if(strlen($this->name . $this->value) > 128) {
			Tracker::_raiseError('Custom Variable combined name and value length must not be larger than 128 bytes.', __METHOD__);
		}
	}
	
	/**
	 * @return int
	 */
	public function getIndex() {
		return $this->index;
	}
	
	/**
	 * @link http://code.google.com/intl/de-DE/apis/analytics/docs/tracking/gaTrackingCustomVariables.html#usage
	 * @param int $index
	 */
	public function setIndex($index) {
		// Custom Variables are limited to five slots officially, but there seems to be a
		// trick to allow for more of them which we could investigate at a later time (see
		// http://analyticsimpact.com/2010/05/24/get-more-than-5-custom-variables-in-google-analytics/)
		if($index < 1 || $index > 5) {
			Tracker::_raiseError('Custom Variable index has to be between 1 and 5.', __METHOD__);
		}
		
		$this->index = (int)$index;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * @param mixed $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}
	
	/**
	 * @return int
	 */
	public function getScope() {
		return $this->scope;
	}
	
	/**
	 * @param int $scope
	 */
	public function setScope($scope) {
		if(!in_array($scope, array(self::SCOPE_PAGE, self::SCOPE_SESSION, self::SCOPE_VISITOR))) {
			Tracker::_raiseError('Custom Variable scope has to be one of the CustomVariable::SCOPE_* constant values.', __METHOD__);
		}
		
		$this->scope = (int)$scope;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

/**
 * @link http://code.google.com/apis/analytics/docs/tracking/eventTrackerOverview.html
 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEventTracking.html
 */
class Event {	
	
	/**
	 * The general event category (e.g. "Videos").
	 * 
	 * @var string
	 */
	protected $category;
	
	/**
	 * The action for the event (e.g. "Play").
	 * 
	 * @var string
	 */
	protected $action;
	
	/**
	 * An optional descriptor for the event (e.g. the video's title).
	 * 
	 * @var string
	 */
	protected $label;
	
	/**
	 * An optional value associated with the event. You can see your event values in the Overview,
	 * Categories, and Actions reports, where they are listed by event or aggregated across events,
	 * depending upon your report view.
	 * 
	 * @var int
	 */
	protected $value;
	
	/**
	 * Default value is false. By default, event hits will impact a visitor's bounce rate.
	 * By setting this parameter to true, this event hit will not be used in bounce rate calculations.
	 * 
	 * @var bool
	 */
	protected $noninteraction = false;
	
	
	/**
	 * @param string $category
	 * @param string $action
	 * @param string $label
	 * @param int $value
	 * @param bool $noninteraction
	 */
	public function __construct($category = null, $action = null, $label = null, $value = null, $noninteraction = null) {
		if($category       !== null) $this->setCategory($category);
		if($action         !== null) $this->setAction($action);
		if($label          !== null) $this->setLabel($label);
		if($value          !== null) $this->setValue($value);
		if($noninteraction !== null) $this->setNoninteraction($noninteraction);
	}
	
	public function validate() {
		if($this->category === null || $this->action === null) {
			Tracker::_raiseError('Events need at least to have a category and action defined.', __METHOD__);
		}
	}
	
	/**
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}
	
	/**
	 * @param string $category
	 */
	public function setCategory($category) {
		$this->category = $category;
	}
	
	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}
	
	/**
	 * @param string $action
	 */
	public function setAction($action) {
		$this->action = $action;
	}
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}
	
	/**
	 * @return int
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * @param int $value
	 */
	public function setValue($value) {
		$this->value = (int)$value;
	}
	
	/**
	 * @return bool
	 */
	public function getNoninteraction() {
		return $this->noninteraction;
	}
	
	/**
	 * @param bool $value
	 */
	public function setNoninteraction($value) {
		$this->noninteraction = (bool)$value;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

/**
 * @see Config::$errorSeverity
 * @see Tracker::_raiseError()
 */
class Exception extends \Exception {
	
}
}





/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

/**
 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/ecommerce/Item.as
 */
class Item {
	
	/**
	 * Order ID, e.g. "a2343898", will be mapped to "utmtid" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmtid
	 * @var string
	 */
	protected $orderId;
	
	/**
	 * Product Code. This is the sku code for a given product, e.g. "989898ajssi",
	 * will be mapped to "utmipc" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmipc
	 * @var string
	 */
	protected $sku;
	
	/**
	 * Product Name, e.g. "T-Shirt", will be mapped to "utmipn" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmipn
	 * @var string
	 */
	protected $name;
	
	/**
	 * Variations on an item, e.g. "white", "black", "green" etc., will be mapped
	 * to "utmiva" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmiva
	 * @var string
	 */
	protected $variation;
	
	/**
	 * Unit Price. Value is set to numbers only (e.g. 19.95), will be mapped to
	 * "utmipr" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmipr
	 * @var float
	 */
	protected $price;
	
	/**
	 * Unit Quantity, e.g. 4, will be mapped to "utmiqt" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmiqt
	 * @var int
	 */
	protected $quantity = 1;
	
	
	public function validate() {
		if($this->sku === null) {
			Tracker::_raiseError('Items need to have a sku/product code defined.', __METHOD__);
		}
	}
	
	/**
	 * @return string
	 */
	public function getOrderId() {
		return $this->orderId;
	}
	
	/**
	 * @param string $orderId
	 */
	public function setOrderId($orderId) {
		$this->orderId = $orderId;
	}
	
	/**
	 * @return string
	 */
	public function getSku() {
		return $this->sku;
	}
	
	/**
	 * @param string $sku
	 */
	public function setSku($sku) {
		$this->sku = $sku;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getVariation() {
		return $this->variation;
	}
	
	/**
	 * @param string $variation
	 */
	public function setVariation($variation) {
		$this->variation = $variation;
	}
	
	/**
	 * @return float
	 */
	public function getPrice() {
		return $this->price;
	}
	
	/**
	 * @param float $price
	 */
	public function setPrice($price) {
		$this->price = (float)$price;
	}
	
	/**
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}
	
	/**
	 * @param int $quantity
	 */
	public function setQuantity($quantity) {
		$this->quantity = (int)$quantity;
	}
	
}

}





/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

class Page {
	
	/**
	 * Page request URI, e.g. "/path/page.html", will be mapped to
	 * "utmp" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmp
	 * @var string
	 */
	protected $path;
	
	/**
	 * Page title, will be mapped to "utmdt" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmdt
	 * @var string
	 */
	protected $title;
	
	/**
	 * Charset encoding (e.g. "UTF-8"), will be mapped to "utmcs" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmcs
	 * @var string
	 */
	protected $charset;
	
	/**
	 * Referer URL, e.g. "http://www.example.com/path/page.html",  will be
	 * mapped to "utmr" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmr
	 * @var string
	 */
	protected $referrer;
	
	/**
	 * Page load time in milliseconds, will be encoded into "utme" parameter.
	 * 
	 * @see Internals\ParameterHolder::$utme
	 * @var int
	 */
	protected $loadTime;
	
	
	/**
	 * Constant to mark referrer as a site-internal one.
	 * 
	 * @see Page::$referrer
	 * @const string
	 */
	const REFERRER_INTERNAL = '0';
	
	
	/**
	 * @param string $path
	 */
	public function __construct($path) {
		$this->setPath($path);
	}
	
	/**
	 * @param string $path
	 */
	public function setPath($path) {
		if($path && $path[0] != '/') {
			Tracker::_raiseError('The page path should always start with a slash ("/").', __METHOD__);
		}
		
		$this->path = $path;
	}
	
	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
	
	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @param string $charset
	 */
	public function setCharset($encoding) {
		$this->charset = $encoding;
	}
	
	/**
	 * @return string
	 */
	public function getCharset() {
		return $this->charset;
	}
	
	/**
	 * @param string $referrer
	 */
	public function setReferrer($referrer) {
		$this->referrer = $referrer;
	}
	
	/**
	 * @return string
	 */
	public function getReferrer() {
		return $this->referrer;
	}
	
	/**
	 * @param int $loadTime
	 */
	public function setLoadTime($loadTime) {
		if((int)$loadTime != (float)$loadTime) {
			return Tracker::_raiseError('Page load time must be specified in integer milliseconds.', __METHOD__);
		}
		
		$this->loadTime = (int)$loadTime;
	}
	
	/**
	 * @return int
	 */
	public function getLoadTime() {
		return $this->loadTime;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

use UnitedPrototype\GoogleAnalytics\Internals\Util;

use DateTime;

/**
 * You should serialize this object and store it in the user session to keep it
 * persistent between requests (similar to the "__umtb" cookie of
 * the GA Javascript client).
 */
class Session {
	
	/**
	 * A unique per-session ID, will be mapped to "utmhid" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmhid
	 * @var int
	 */
	protected $sessionId;
	
	/**
	 * The amount of pageviews that were tracked within this session so far,
	 * will be part of the "__utmb" cookie parameter.
	 * 
	 * Will get incremented automatically upon each request.
	 * 
	 * @see Internals\ParameterHolder::$__utmb
	 * @see Internals\Request\Request::buildHttpRequest()
	 * @var int
	 */
	protected $trackCount;
	
	/**
	 * Timestamp of the start of this new session, will be part of the "__utmb"
	 * cookie parameter
	 * 
	 * @see Internals\ParameterHolder::$__utmb
	 * @var DateTime
	 */
	protected $startTime;
	
	
	public function __construct() {
		$this->setSessionId($this->generateSessionId());
		$this->setTrackCount(0);
		$this->setStartTime(new DateTime());
	}
	
	/**
	 * Will extract information for the "trackCount" and "startTime"
	 * properties from the given "__utmb" cookie value.
	 * 
	 * @see Internals\ParameterHolder::$__utmb
	 * @see Internals\Request\Request::buildCookieParameters()
	 * @param string $value
	 * @return $this
	 */
	public function fromUtmb($value) {
		$parts = explode('.', $value);
		if(count($parts) != 4) {
			Tracker::_raiseError('The given "__utmb" cookie value is invalid.', __METHOD__);
			return $this;
		}
		
		$this->setTrackCount($parts[1]);
		$this->setStartTime(new DateTime('@' . $parts[3]));
		
		// Allow chaining
		return $this;
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/DocumentInfo.as#52
	 * @return int
	 */
	protected function generateSessionId() {
		// TODO: Integrate AdSense support
		return Util::generate32bitRandom();
	}
	
	/**
	 * @return int
	 */
	public function getSessionId() {
		return $this->sessionId;
	}
	
	/**
	 * @param int $sessionId
	 */
	public function setSessionId($sessionId) {
		$this->sessionId = $sessionId;
	}
	
	/**
	 * @return int
	 */
	public function getTrackCount() {
		return $this->trackCount;
	}
	
	/**
	 * @param int $trackCount
	 */
	public function setTrackCount($trackCount) {
		$this->trackCount = (int)$trackCount;
	}
	
	/**
	 * @param int $byAmount
	 */
	public function increaseTrackCount($byAmount = 1) {
		$this->trackCount += $byAmount;
	}
	
	/**
	 * @return DateTime
	 */
	public function getStartTime() {
		return $this->startTime;
	}
	
	/**
	 * @param DateTime $startTime
	 */
	public function setStartTime(DateTime $startTime) {
		$this->startTime = $startTime;
	}

}

}



/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

class SocialInteraction {
	
	/**
	 * Required. A string representing the social network being tracked (e.g. "Facebook", "Twitter", "LinkedIn", ...),
	 * will be mapped to "utmsn" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmsn
	 * @var string
	 */
	protected $network;
	
	/**
	 * Required. A string representing the social action being tracked (e.g. "Like", "Share", "Tweet", ...),
	 * will be mapped to "utmsa" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmsa
	 * @var string
	 */
	protected $action;
	
	/**
	 * Optional. A string representing the URL (or resource) which receives the action. For example,
	 * if a user clicks the Like button on a page on a site, the the target might be set to the title
	 * of the page, or an ID used to identify the page in a content management system. In many cases,
	 * the page you Like is the same page you are on. So if this parameter is not given, we will default
	 * to using the path of the corresponding Page object.
	 * 
	 * @see Internals\ParameterHolder::$utmsid
	 * @var string
	 */
	protected $target;
	
	
	/**
	 * @param string $path
	 */
	public function __construct($network = null, $action = null, $target = null) {
		if($network !== null) $this->setNetwork($network);
		if($action  !== null) $this->setAction($action);
		if($target  !== null) $this->setTarget($target);
	}
	
	public function validate() {
		if($this->network === null || $this->action === null) {
			Tracker::_raiseError('Social interactions need to have at least the "network" and "action" attributes defined.', __METHOD__);
		}
	}
	
	/**
	 * @param string $network
	 */
	public function setNetwork($network) {
		$this->network = $network;
	}
	
	/**
	 * @return string
	 */
	public function getNetwork() {
		return $this->network;
	}
	
	/**
	 * @param string $action
	 */
	public function setAction($action) {
		$this->action = $action;
	}
	
	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}
	
	/**
	 * @param string $target
	 */
	public function setTarget($target) {
		$this->target = $target;
	}
	
	/**
	 * @return string
	 */
	public function getTarget() {
		return $this->target;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

use UnitedPrototype\GoogleAnalytics\Internals\Util;
use UnitedPrototype\GoogleAnalytics\Internals\Request\PageviewRequest;
use UnitedPrototype\GoogleAnalytics\Internals\Request\EventRequest;
use UnitedPrototype\GoogleAnalytics\Internals\Request\TransactionRequest;
use UnitedPrototype\GoogleAnalytics\Internals\Request\ItemRequest;
use UnitedPrototype\GoogleAnalytics\Internals\Request\SocialInteractionRequest;

class Tracker {
	
	/**
	 * Google Analytics client version on which this library is built upon,
	 * will be mapped to "utmwv" parameter.
	 * 
	 * This doesn't necessarily mean that all features of the corresponding
	 * ga.js version are implemented but rather that the requests comply
	 * with these of ga.js.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/gaJS/changelog.html
	 * @const string
	 */
	const VERSION = '5.2.5'; // As of 25.02.2012
	
	
	/**
	 * The configuration to use for all tracker instances.
	 * 
	 * @var \UnitedPrototype\GoogleAnalytics\Config
	 */
	protected static $config;
	
	/**
	 * Google Analytics account ID, e.g. "UA-1234567-8", will be mapped to
	 * "utmac" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmac
	 * @var string
	 */
	protected $accountId;
	
	/**
	 * Host Name, e.g. "www.example.com", will be mapped to "utmhn" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmhn
	 * @var string
	 */
	protected $domainName;
	
	/**
	 * Whether to generate a unique domain hash, default is true to be consistent
	 * with the GA Javascript Client
	 * 
	 * @link http://code.google.com/apis/analytics/docs/tracking/gaTrackingSite.html#setAllowHash
	 * @see Internals\Request\Request::generateDomainHash()
	 * @var bool
	 */
	protected $allowHash = true;
	
	/**
	 * @var array
	 */
	protected $customVariables = array();
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\Campaign
	 */
	protected $campaign;
	
	
	/**
	 * @param string $accountId
	 * @param string $domainName
	 * @param \UnitedPrototype\GoogleAnalytics\Config $config
	 */
	public function __construct($accountId, $domainName, Config $config = null) {
		static::setConfig($config ? $config : new Config());
		
		$this->setAccountId($accountId);
		$this->setDomainName($domainName);
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Config
	 */
	public static function getConfig() {
		return static::$config;
	}	
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Config $value
	 */
	public static function setConfig(Config $value) {
		static::$config = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setAccountId($value) {
		if(!preg_match('/^(UA|MO)-[0-9]*-[0-9]*$/', $value)) {
			static::_raiseError('"' . $value . '" is not a valid Google Analytics account ID.', __METHOD__);
		}
		
		$this->accountId = $value;
	}
	
	/**
	 * @return string
	 */
	public function getAccountId() {
		return $this->accountId;
	}
	
	/**
	 * @param string $value
	 */
	public function setDomainName($value) {
		$this->domainName = $value;
	}
	
	/**
	 * @return string
	 */
	public function getDomainName() {
		return $this->domainName;
	}
	
	/**
	 * @param bool $value
	 */
	public function setAllowHash($value) {
		$this->allowHash = (bool)$value;
	}
	
	/**
	 * @return bool
	 */
	public function getAllowHash() {
		return $this->allowHash;
	}
	
	/**
	 * Equivalent of _setCustomVar() in GA Javascript client.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/tracking/gaTrackingCustomVariables.html
	 * @param \UnitedPrototype\GoogleAnalytics\CustomVariable $customVariable
	 */
	public function addCustomVariable(CustomVariable $customVariable) {
		// Ensure that all required parameters are set
		$customVariable->validate();
		
		$index = $customVariable->getIndex();
		$this->customVariables[$index] = $customVariable;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\CustomVariable[]
	 */
	public function getCustomVariables() {
		return $this->customVariables;
	}
	
	/**
	 * Equivalent of _deleteCustomVar() in GA Javascript client.
	 * 
	 * @param int $index
	 */
	public function removeCustomVariable($index) {
		unset($this->customVariables[$index]);
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Campaign $campaign Isn't really optional, but can be set to null
	 */
	public function setCampaign(Campaign $campaign = null) {
		if($campaign) {
			// Ensure that all required parameters are set
			$campaign->validate();
		}
		
		$this->campaign = $campaign;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Campaign|null
	 */
	public function getCampaign() {
		return $this->campaign;
	}
	
	/**
	 * Equivalent of _trackPageview() in GA Javascript client.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._trackPageview
	 * @param \UnitedPrototype\GoogleAnalytics\Page $page
	 * @param \UnitedPrototype\GoogleAnalytics\Session $session
	 * @param \UnitedPrototype\GoogleAnalytics\Visitor $visitor
	 */
	public function trackPageview(Page $page, Session $session, Visitor $visitor) {
		$request = new PageviewRequest(static::$config);
		$request->setPage($page);
		$request->setSession($session);
		$request->setVisitor($visitor);
		$request->setTracker($this);
		$request->fire();
	}
	
	/**
	 * Equivalent of _trackEvent() in GA Javascript client.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEventTracking.html#_gat.GA_EventTracker_._trackEvent
	 * @param \UnitedPrototype\GoogleAnalytics\Event $event
	 * @param \UnitedPrototype\GoogleAnalytics\Session $session
	 * @param \UnitedPrototype\GoogleAnalytics\Visitor $visitor
	 */
	public function trackEvent(Event $event, Session $session, Visitor $visitor) {
		// Ensure that all required parameters are set
		$event->validate();
		
		$request = new EventRequest(static::$config);
		$request->setEvent($event);
		$request->setSession($session);
		$request->setVisitor($visitor);
		$request->setTracker($this);
		$request->fire();
	}
	
	/**
	 * Combines _addTrans(), _addItem() (indirectly) and _trackTrans() of GA Javascript client.
	 * Although the naming of "_addTrans()" would suggest multiple possible transactions
	 * per request, there is just one allowed actually.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addTrans
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addItem
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._trackTrans
	 * 
	 * @param \UnitedPrototype\GoogleAnalytics\Transaction $transaction
	 * @param \UnitedPrototype\GoogleAnalytics\Session $session
	 * @param \UnitedPrototype\GoogleAnalytics\Visitor $visitor
	 */
	public function trackTransaction(Transaction $transaction, Session $session, Visitor $visitor) {
		// Ensure that all required parameters are set
		$transaction->validate();
		
		$request = new TransactionRequest(static::$config);
		$request->setTransaction($transaction);
		$request->setSession($session);
		$request->setVisitor($visitor);
		$request->setTracker($this);
		$request->fire();
		
		// Every item gets a separate request,
		// see http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/v4/Tracker.as#312
		foreach($transaction->getItems() as $item) {
			// Ensure that all required parameters are set
			$item->validate();
			
			$request = new ItemRequest(static::$config);
			$request->setItem($item);
			$request->setSession($session);
			$request->setVisitor($visitor);
			$request->setTracker($this);
			$request->fire();
		}
	}
	
	/**
	 * Equivalent of _trackSocial() in GA Javascript client.
	 * 
	 * @link http://code.google.com/apis/analytics/docs/tracking/gaTrackingSocial.html#settingUp
	 * @param \UnitedPrototype\GoogleAnalytics\SocialInteraction $socialInteraction
	 * @param \UnitedPrototype\GoogleAnalytics\Page $page
	 * @param \UnitedPrototype\GoogleAnalytics\Session $session
	 * @param \UnitedPrototype\GoogleAnalytics\Visitor $visitor
	 */
	public function trackSocial(SocialInteraction $socialInteraction, Page $page, Session $session, Visitor $visitor) {
		$request = new SocialInteractionRequest(static::$config);
		$request->setSocialInteraction($socialInteraction);
		$request->setPage($page);
		$request->setSession($session);
		$request->setVisitor($visitor);
		$request->setTracker($this);
		$request->fire();
	}
	
	/**
	 * For internal use only. Will trigger an error according to the current
	 * Config::$errorSeverity setting.
	 * 
	 * @see Config::$errorSeverity
	 * @param string $message
	 * @param string $method
	 */
	public static function _raiseError($message, $method) {
		$method = str_replace(__NAMESPACE__ . '\\', '', $method);
		$message = $method . '(): ' . $message;
		
		$errorSeverity = isset(static::$config) ? static::$config->getErrorSeverity() : Config::ERROR_SEVERITY_EXCEPTIONS;
		
		switch($errorSeverity) {
			case Config::ERROR_SEVERITY_SILENCE:
				// Do nothing
				break;
			case Config::ERROR_SEVERITY_WARNINGS:
				trigger_error($message, E_USER_WARNING);
				break;
			case Config::ERROR_SEVERITY_EXCEPTIONS:
				throw new Exception($message);
				break;
		}
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

/**
 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/ecommerce/Transaction.as
 */
class Transaction {
	
	/**
	 * Order ID, e.g. "a2343898", will be mapped to "utmtid" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmtid
	 * @var string
	 */
	protected $orderId;
	
	/**
	 * Affiliation, Will be mapped to "utmtst" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmtst
	 * @var string
	 */
	protected $affiliation;
	
	/**
	 * Total Cost, will be mapped to "utmtto" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmtto
	 * @var float
	 */
	protected $total;
	
	/**
	 * Tax Cost, will be mapped to "utmttx" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmttx
	 * @var float
	 */
	protected $tax;
	
	/**
	 * Shipping Cost, values as for unit and price, e.g. 3.95, will be mapped to
	 * "utmtsp" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmtsp
	 * @var float
	 */
	protected $shipping;
	
	/**
	 * Billing City, e.g. "Cologne", will be mapped to "utmtci" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmtci
	 * @var string
	 */
	protected $city;
	
	/**
	 * Billing Region, e.g. "North Rhine-Westphalia", will be mapped to "utmtrg" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmtrg
	 * @var string
	 */
	protected $region;
	
	/**
	 * Billing Country, e.g. "Germany", will be mapped to "utmtco" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmtco
	 * @var string
	 */
	protected $country;
	
	/**
	 * @see Transaction::addItem()
	 * @var \UnitedPrototype\GoogleAnalytics\Item[]
	 */
	protected $items = array();
	
	
	public function validate() {
		if(!$this->items) {
			Tracker::_raiseError('Transactions need to consist of at least one item.', __METHOD__);
		}
	}
	
	/**
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addItem
	 * @param \UnitedPrototype\GoogleAnalytics\Item $item
	 */
	public function addItem(Item $item) {
		// Associated items inherit the transaction's order ID
		$item->setOrderId($this->orderId);
		
		$sku = $item->getSku();
		$this->items[$sku] = $item;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Item[]
	 */
	public function getItems() {
		return $this->items;
	}
	
	/**
	 * @return string
	 */
	public function getOrderId() {
		return $this->orderId;
	}
	
	/**
	 * @param string $orderId
	 */
	public function setOrderId($orderId) {
		$this->orderId = $orderId;
		
		// Update order IDs of all associated items too
		foreach($this->items as $item) {
			$item->setOrderId($orderId);
		}
	}
	
	/**
	 * @return string
	 */
	public function getAffiliation() {
		return $this->affiliation;
	}
	
	/**
	 * @param string $affiliation
	 */
	public function setAffiliation($affiliation) {
		$this->affiliation = $affiliation;
	}
	
	/**
	 * @return float
	 */
	public function getTotal() {
		return $this->total;
	}
	
	/**
	 * @param float $total
	 */
	public function setTotal($total) {
		$this->total = $total;
	}
	
	/**
	 * @return float
	 */
	public function getTax() {
		return $this->tax;
	}
	
	/**
	 * @param float $tax
	 */
	public function setTax($tax) {
		$this->tax = $tax;
	}
	
	/**
	 * @return float
	 */
	public function getShipping() {
		return $this->shipping;
	}
	
	/**
	 * @param float $shipping
	 */
	public function setShipping($shipping) {
		$this->shipping = $shipping;
	}
	
	/**
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}
	
	/**
	 * @param string $city
	 */
	public function setCity($city) {
		$this->city = $city;
	}
	
	/**
	 * @return string
	 */
	public function getRegion() {
		return $this->region;
	}
	
	/**
	 * @param string $region
	 */
	public function setRegion($region) {
		$this->region = $region;
	}
	
	/**
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}
	
	/**
	 * @param string $country
	 */
	public function setCountry($country) {
		$this->country = $country;
	}
	
}

}






/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics{

use UnitedPrototype\GoogleAnalytics\Internals\Util;

use DateTime;

/**
 * You should serialize this object and store it in the user database to keep it
 * persistent for the same user permanently (similar to the "__umta" cookie of
 * the GA Javascript client).
 */
class Visitor {
	
	/**
	 * Unique user ID, will be part of the "__utma" cookie parameter
	 * 
	 * @see Internals\ParameterHolder::$__utma
	 * @var int
	 */
	protected $uniqueId;
	
	/**
	 * Time of the very first visit of this user, will be part of the "__utma"
	 * cookie parameter
	 * 
	 * @see Internals\ParameterHolder::$__utma
	 * @var DateTime
	 */
	protected $firstVisitTime;
	
	/**
	 * Time of the previous visit of this user, will be part of the "__utma"
	 * cookie parameter
	 * 
	 * @see Internals\ParameterHolder::$__utma
	 * @see addSession
	 * @var DateTime
	 */
	protected $previousVisitTime;
	
	/**
	 * Time of the current visit of this user, will be part of the "__utma"
	 * cookie parameter
	 * 
	 * @see Internals\ParameterHolder::$__utma
	 * @see addSession
	 * @var DateTime
	 */
	protected $currentVisitTime;
	
	/**
	 * Amount of total visits by this user, will be part of the "__utma"
	 * cookie parameter
	 * 
	 * @see Internals\ParameterHolder::$__utma
	 * @var int
	 */
	protected $visitCount;
	
	/**
	 * IP Address of the end user, e.g. "123.123.123.123", will be mapped to "utmip" parameter
	 * and "X-Forwarded-For" request header
	 * 
	 * @see Internals\ParameterHolder::$utmip
	 * @see Internals\Request\HttpRequest::$xForwardedFor
	 * @var string
	 */
	protected $ipAddress;
	
	/**
	 * User agent string of the end user, will be mapped to "User-Agent" request header
	 * 
	 * @see Internals\Request\HttpRequest::$userAgent
	 * @var string
	 */
	protected $userAgent;
	
	/**
	 * Locale string (country part optional), e.g. "de-DE", will be mapped to "utmul" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmul
	 * @var string
	 */
	protected $locale;
	
	/**
	 * Visitor's Flash version, e.g. "9.0 r28", will be maped to "utmfl" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmfl
	 * @var string
	 */
	protected $flashVersion;
	
	/**
	 * Visitor's Java support, will be mapped to "utmje" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmje
	 * @var bool
	 */
	protected $javaEnabled;
	
	/**
	 * Visitor's screen color depth, e.g. 32, will be mapped to "utmsc" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmsc
	 * @var string
	 */
	protected $screenColorDepth;
	
	/**
	 * Visitor's screen resolution, e.g. "1024x768", will be mapped to "utmsr" parameter
	 * 
	 * @see Internals\ParameterHolder::$utmsr
	 * @var string
	 */
	protected $screenResolution;
	
	
	/**
	 * Creates a new visitor without any previous visit information.
	 */
	public function __construct() {
		// ga.js sets all three timestamps to now for new visitors, so we do the same
		$now = new DateTime();
		$this->setFirstVisitTime($now);
		$this->setPreviousVisitTime($now);
		$this->setCurrentVisitTime($now);
		
		$this->setVisitCount(1);
	}
	
	/**
	 * Will extract information for the "uniqueId", "firstVisitTime", "previousVisitTime",
	 * "currentVisitTime" and "visitCount" properties from the given "__utma" cookie
	 * value.
	 * 
	 * @see Internals\ParameterHolder::$__utma
	 * @see Internals\Request\Request::buildCookieParameters()
	 * @param string $value
	 * @return $this
	 */
	public function fromUtma($value) {
		$parts = explode('.', $value);
		if(count($parts) != 6) {
			Tracker::_raiseError('The given "__utma" cookie value is invalid.', __METHOD__);
			return $this;
		}
		
		$this->setUniqueId($parts[1]);
		$this->setFirstVisitTime(new DateTime('@' . $parts[2]));
		$this->setPreviousVisitTime(new DateTime('@' . $parts[3]));
		$this->setCurrentVisitTime(new DateTime('@' . $parts[4]));
		$this->setVisitCount($parts[5]);
		
		// Allow chaining
		return $this;
	}
	
	/**
	 * Will extract information for the "ipAddress", "userAgent" and "locale" properties
	 * from the given $_SERVER variable.
	 * 
	 * @param array $value
	 * @return $this
	 */
	public function fromServerVar(array $value) {
		if(!empty($value['REMOTE_ADDR'])) {
			$ip = null;
			foreach(array('X_FORWARDED_FOR', 'REMOTE_ADDR') as $key) {
				if(!empty($value[$key]) && !$ip) {
					$ips = explode(',', $value[$key]);
					$ip  = trim($ips[(count($ips) - 1)]);
					
					// Double-check if the address has a valid format
					if(!preg_match('/^[\d+]{1,3}\.[\d+]{1,3}\.[\d+]{1,3}\.[\d+]{1,3}$/i', $ip)) {
						$ip = null;
					}
					// Exclude private IP address ranges
					if(preg_match('#^(?:127\.0\.0\.1|10\.|192\.168\.|172\.(?:1[6-9]|2[0-9]|3[0-1])\.)#', $ip)) {
						$ip = null;
					}
				}
			}
			
			if($ip) {
				$this->setIpAddress($ip);
			}
		}
		
		if(!empty($value['HTTP_USER_AGENT'])) {
			$this->setUserAgent($value['HTTP_USER_AGENT']);
		}
		
		if(!empty($value['HTTP_ACCEPT_LANGUAGE'])) {
			$parsedLocales = array();
			if(preg_match_all('/(^|\s*,\s*)([a-zA-Z]{1,8}(-[a-zA-Z]{1,8})*)\s*(;\s*q\s*=\s*(1(\.0{0,3})?|0(\.[0-9]{0,3})))?/i', $value['HTTP_ACCEPT_LANGUAGE'], $matches)) {
				$matches[2] = array_map(function($part) { return str_replace('-', '_', $part); }, $matches[2]);
				$matches[5] = array_map(function($part) { return $part === '' ? 1 : $part; }, $matches[5]);
				$parsedLocales = array_combine($matches[2], $matches[5]);
				arsort($parsedLocales, SORT_NUMERIC);
				$parsedLocales = array_keys($parsedLocales);
			}
			
			if($parsedLocales) {
				$this->setLocale($parsedLocales[0]);
			}
		}
		
		// Allow chaining
		return $this;
	}
	
	/**
	 * Generates a hashed value from user-specific properties.
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/v4/Tracker.as#542
	 * @return int
	 */
	protected function generateHash() {
		// TODO: Emulate orginal Google Analytics client library generation more closely
		$string  = $this->userAgent;
		$string .= $this->screenResolution . $this->screenColorDepth;
		return Util::generateHash($string);
	}
	
	/**
	 * Generates a unique user ID from the current user-specific properties.
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/v4/Tracker.as#563
	 * @return int
	 */
	protected function generateUniqueId() {
		// There seems to be an error in the gaforflash code, so we take the formula
		// from http://xahlee.org/js/google_analytics_tracker_2010-07-01_expanded.js line 711
		// instead ("&" instead of "*")
		return ((Util::generate32bitRandom() ^ $this->generateHash()) & 0x7fffffff);
	}
	
	/**
	 * @see generateUniqueId
	 * @param int $value
	 */
	public function setUniqueId($value) {
		if($value < 0 || $value > 0x7fffffff) {
			Tracker::_raiseError('Visitor unique ID has to be a 32-bit integer between 0 and ' . 0x7fffffff . '.', __METHOD__);
		}
		
		$this->uniqueId = (int)$value;
	}
	
	/**
	 * Will be generated on first call (if not set already) to include as much
	 * user-specific information as possible.
	 * 
	 * @return int
	 */
	public function getUniqueId() {
		if($this->uniqueId === null) {
			$this->uniqueId = $this->generateUniqueId();
		}
		return $this->uniqueId;
	}
	
	/**
	 * Updates the "previousVisitTime", "currentVisitTime" and "visitCount"
	 * fields based on the given session object.
	 * 
	 * @param Session $session
	 */
	public function addSession(Session $session) {
		$startTime = $session->getStartTime();
		if($startTime != $this->currentVisitTime) {
			$this->previousVisitTime = $this->currentVisitTime;
			$this->currentVisitTime  = $startTime;
			++$this->visitCount;
		}
	}
	
	/**
	 * @param DateTime $value
	 */
	public function setFirstVisitTime(DateTime $value) {
		$this->firstVisitTime = $value;
	}
	
	/**
	 * @return DateTime
	 */
	public function getFirstVisitTime() {
		return $this->firstVisitTime;
	}
	
	/**
	 * @param DateTime $value
	 */
	public function setPreviousVisitTime(DateTime $value) {
		$this->previousVisitTime = $value;
	}
	
	/**
	 * @return DateTime
	 */
	public function getPreviousVisitTime() {
		return $this->previousVisitTime;
	}
	
	/**
	 * @param DateTime $value
	 */
	public function setCurrentVisitTime(DateTime $value) {
		$this->currentVisitTime = $value;
	}
	
	/**
	 * @return DateTime
	 */
	public function getCurrentVisitTime() {
		return $this->currentVisitTime;
	}
	
	/**
	 * @param int $value
	 */
	public function setVisitCount($value) {
		$this->visitCount = (int)$value;
	}
	
	/**
	 * @return int
	 */
	public function getVisitCount() {
		return $this->visitCount;
	}
	
	/**
	 * @param string $value
	 */
	public function setIpAddress($value) {
		$this->ipAddress = $value;
	}
	
	/**
	 * @return string
	 */
	public function getIpAddress() {
		return $this->ipAddress;
	}
	
	/**
	 * @param string $value
	 */
	public function setUserAgent($value) {
		$this->userAgent = $value;
	}
	
	/**
	 * @return string
	 */
	public function getUserAgent() {
		return $this->userAgent;
	}
	
	/**
	 * @param string $value
	 */
	public function setLocale($value) {
		$this->locale = $value;
	}
	
	/**
	 * @return string
	 */
	public function getLocale() {
		return $this->locale;
	}
	
	/**
	 * @param string $value
	 */
	public function setFlashVersion($value) {
		$this->flashVersion = $value;
	}
	
	/**
	 * @return string
	 */
	public function getFlashVersion() {
		return $this->flashVersion;
	}
	
	/**
	 * @param bool $value
	 */
	public function setJavaEnabled($value) {
		$this->javaEnabled = (bool)$value;
	}
	
	/**
	 * @return bool
	 */
	public function getJavaEnabled() {
		return $this->javaEnabled;
	}
	
	/**
	 * @param int $value
	 */
	public function setScreenColorDepth($value) {
		$this->screenColorDepth = (int)$value;
	}
	
	/**
	 * @return string
	 */
	public function getScreenColorDepth() {
		return $this->screenColorDepth;
	}
	
	/**
	 * @param string $value
	 */
	public function setScreenResolution($value) {
		$this->screenResolution = $value;
	}
	
	/**
	 * @return string
	 */
	public function getScreenResolution() {
		return $this->screenResolution;
	}
	
}
}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals{

/**
 * This is nearly a 1:1 PHP port of the gaforflash X10 class code.
 * 
 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/data/X10.as
 */
class X10 {
	
	/**
	 * @var array
	 */
	protected $projectData = array();
	
	
	/**
	 * @var string
	 */
	protected $KEY = 'k';
	
	/**
	 * @var string
	 */
	protected $VALUE = 'v';
	
	/**
	 * @var array
	 */
	protected $SET = array('k', 'v');
	
	/**
	 * Opening delimiter for wrapping a set of values belonging to the same type.
	 * @var string
	 */
	protected $DELIM_BEGIN = '(';
	
	/**
	 * Closing delimiter for wrapping a set of values belonging to the same type.
	 * @var string
	 */
	protected $DELIM_END   = ')';
	
	/**
	 * Delimiter between two consecutive num/value pairs.
	 * @var string
	 */
	protected $DELIM_SET = '*';
	
	/**
	 * Delimiter between a num and its corresponding value.
	 * @var string
	 */
	protected $DELIM_NUM_VALUE = '!';
	
	/**
	 * Mapping of escapable characters to their escaped forms.
	 * 
	 * @var array
	 */
	protected $ESCAPE_CHAR_MAP = array(
		"'" => "'0",
		')' => "'1",
		'*' => "'2",
		'!' => "'3",
	);
	
	/**
	 * @var int
	 */
	protected $MINIMUM = 1;
	
	
	/**
	 * @const int
	 */
	const OBJECT_KEY_NUM  = 1;
	/**
	 * @const int
	 */
	const TYPE_KEY_NUM    = 2;
	/**
	 * @const int
	 */
	const LABEL_KEY_NUM   = 3;
	/**
	 * @const int
	 */
	const VALUE_VALUE_NUM = 1;
	
	
	/**
	 * @param int $projectId
	 * @return bool
	 */
	protected function hasProject($projectId) {
		return isset($this->projectData[$projectId]);
	}
	
	/**
	 * @param int $projectId
	 * @param int $num
	 * @param mixed $value
	 */
	public function setKey($projectId, $num, $value) {
		$this->setInternal($projectId, $this->KEY, $num, $value);
	}
	
	/**
	 * @param int $projectId
	 * @param int $num
	 * @return mixed
	 */
	public function getKey($projectId, $num) {
		return $this->getInternal($projectId, $this->KEY, $num);
	}
	
	/**
	 * @param int $projectId
	 */
	public function clearKey($projectId) {
		$this->clearInternal($projectId, $this->KEY);
	}
	
	/**
	 * @param int $projectId
	 * @param int $num
	 * @param mixed $value
	 */
	public function setValue($projectId, $num, $value) {
		$this->setInternal($projectId, $this->VALUE, $num, $value);
	}
	
	/**
	 * @param int $projectId
	 * @param int $num
	 * @return mixed
	 */
	public function getValue($projectId, $num) {
		return $this->getInternal($projectId, $this->VALUE, $num);
	}
	
	/**
	 * @param int $projectId
	 */
	public function clearValue($projectId) {
		$this->clearInternal($projectId, $this->VALUE);
	}
	
	/**
	 * Shared internal implementation for setting an X10 data type.
	 * 
	 * @param int $projectId
	 * @param string $type
	 * @param int $num
	 * @param mixed $value
	 */
	protected function setInternal($projectId, $type, $num, $value) {
		if(!isset($this->projectData[$projectId])) {
			$this->projectData[$projectId] = array();
		}
		if(!isset($this->projectData[$projectId][$type])) {
			$this->projectData[$projectId][$type] = array();
		}
		
		$this->projectData[$projectId][$type][$num] = $value;
	}
	
	/**
	 * Shared internal implementation for getting an X10 data type.
	 * 
	 * @param int $projectId
	 * @param string $type
	 * @param int $num
	 * @return mixed
	 */
	protected function getInternal($projectId, $type, $num) {
		if(isset($this->projectData[$projectId][$type][$num])) {
			return $this->projectData[$projectId][$type][$num];
		} else {
			return null;
		}
	}
	
	/**
	 * Shared internal implementation for clearing all X10 data of a type from a
	 * certain project.
	 * 
	 * @param int $projectId
	 * @param string $type
	 */
	protected function clearInternal($projectId, $type) {
		if(isset($this->projectData[$projectId]) && isset($this->projectData[$projectId][$type])) {
			unset($this->projectData[$projectId][$type]);
		}
	}
	
	/**
	 * Escape X10 string values to remove ambiguity for special characters.
	 *
	 * @see X10::$escapeCharMap
	 * @param string $value
	 * @return string
	 */
	protected function escapeExtensibleValue($value) {
		$result = '';
		
		$value = (string)$value;
		$length = strlen($value);
		for($i = 0; $i < $length; $i++) {
			$char = $value[$i];
			
			if(isset($this->ESCAPE_CHAR_MAP[$char])) {
				$result .= $this->ESCAPE_CHAR_MAP[$char];
			} else {
				$result .= $char;
			}
		}
		
		return $result;
	}
	
	/**
	 * Given a data array for a certain type, render its string encoding.
	 * 
	 * @param array $data
	 * @return string
	 */
	protected function renderDataType(array $data) {
		$result = array();
		
		$lastI = 0;
		ksort($data, SORT_NUMERIC);
		foreach($data as $i => $entry) {
			if(isset($entry)) {
				$str = '';
				
				// Check if we need to append the number. If the last number was
				// outputted, or if this is the assumed minimum, then we don't.
				if($i != $this->MINIMUM && $i - 1 != $lastI) {
					$str .= $i;
					$str .= $this->DELIM_NUM_VALUE;
				}
	
				$str .= $this->escapeExtensibleValue($entry);
				$result[] = $str;
			}
			
			$lastI = $i;
		}
		
		return $this->DELIM_BEGIN . implode($this->DELIM_SET, $result) . $this->DELIM_END;
	}
	
	/**
	 * Given a project array, render its string encoding.
	 * 
	 * @param array $project
	 * @return string
	 */
	protected function renderProject(array $project) {
		$result = '';
	
		// Do we need to output the type string? As an optimization we do not
		// output the type string if it's the first type, or if the previous
		// type was present.
		$needTypeQualifier = false;
		
		$length = count($this->SET);
		for($i = 0; $i < $length; $i++) {
			if(isset($project[$this->SET[$i]])) {
				$data = $project[$this->SET[$i]];
				
				if($needTypeQualifier) {
					$result .= $this->SET[$i];
				}
				$result .= $this->renderDataType($data);
				$needTypeQualifier = false;
			} else {
				$needTypeQualifier = true;
			}
		}
		
		return $result;
	}
	
	/**
	 * Generates the URL parameter string for the current internal extensible data state.
	 * 
	 * @return string
	 */
	public function renderUrlString() {
		$result = '';
		
		foreach($this->projectData as $projectId => $project) {
			$result .= $projectId . $this->renderProject($project);
		}
		
		return $result;
	}
	
}

}





/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals{

/**
 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/Utils.as
 */
class Util {
	
	/**
	 * This class does only have public static methods, no instantiation necessary
	 */
	private function __construct() { }
	
	
	/**
	 * Mimics Javascript's encodeURIComponent() function for consistency with the GA Javascript client.
	 * 
	 * @param mixed $value
	 * @return string
	 */
	public static function encodeUriComponent($value) {
		return static::convertToUriComponentEncoding(rawurlencode($value));
	}
	
	/**
	 * Here as a separate method so it can also be applied to e.g. a http_build_query() result.
	 *  
	 * @link http://stackoverflow.com/questions/1734250/what-is-the-equivalent-of-javascripts-encodeuricomponent-in-php/1734255#1734255
	 * @link http://devpro.it/examples/php_js_escaping.php
	 * 
	 * @param string $encodedValue
	 * @return string
	 */
	public static function convertToUriComponentEncoding($encodedValue) {
		return str_replace(array('%21', '%2A', '%27', '%28', '%29'), array('!', '*', "'", '(', ')'), $encodedValue);
	}
	
	/**
	 * Generates a 32bit random number.
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/Utils.as#33
	 * @return int
	 */
	public static function generate32bitRandom() {
		return round((rand() / getrandmax()) * 0x7fffffff);
	}
	
	/**
	 * Generates a hash for input string.
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/Utils.as#44
	 * @param string $string
	 * @return int
	 */
	public static function generateHash($string) {
		$string = (string)$string;
		$hash = 1;
		
		if($string !== null && $string !== '') {
			$hash = 0;
			
			$length = strlen($string);
			for($pos = $length - 1; $pos >= 0; $pos--) {
				$current   = ord($string[$pos]);
				$hash      = (($hash << 6) & 0xfffffff) + $current + ($current << 14);
				$leftMost7 = $hash & 0xfe00000;
				if($leftMost7 != 0) {
					$hash ^= $leftMost7 >> 21;
				}
			}
		}
		
		return $hash;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals{

use UnitedPrototype\GoogleAnalytics\Tracker;

/**
 * This simple class is mainly meant to be a well-documented overview of all
 * possible GA tracking parameters.
 * 
 * @link http://code.google.com/apis/analytics/docs/tracking/gaTrackingTroubleshooting.html#gifParameters
 */
class ParameterHolder {	
	
	// - - - - - - - - - - - - - - - - - General parameters - - - - - - - - - - - - - - - - -
	
	/**
	 * Google Analytics client version, e.g. "4.7.2"
	 * @var string
	 */
	public $utmwv = Tracker::VERSION;
	
	/**
	 * Google Analytics account ID, e.g. "UA-1234567-8"
	 * @var string
	 */
	public $utmac;
	
	/**
	 * Host Name, e.g. "www.example.com"
	 * @var string
	 */
	public $utmhn;
	
	/**
	 * Indicates the type of request, which is one of null (for page), "event",
	 * "tran", "item", "social", "var" (deprecated) or "error" (used by ga.js
	 * for internal client error logging).
	 * @var string
	 */
	public $utmt;
	
	/**
	 * Contains the amount of requests done in this session. Added in ga.js v4.9.2.
	 * @var int
	 */
	public $utms;
	
	/**
	 * Unique ID (random number) generated for each GIF request
	 * @var int
	 */
	public $utmn;
	
	/**
	 * Contains all cookie values, see below
	 * @var string
	 */
	public $utmcc;
	
	/**
	 * Extensible Parameter, used for events and custom variables
	 * @var string
	 */
	public $utme;
	
	/**
	 * Event "non-interaction" parameter. By default, the event hit will impact a visitor's bounce rate.
	 * By setting this parameter to 1, this event hit will not be used in bounce rate calculations.
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEventTracking.html
	 * @var int
	 */
	public $utmni;
	
	/**
	 * Whether to anonymize IP addresses within Google Analytics by stripping
	 * the last IP address block, either null or 1
	 * @var int
	 */
	public $aip;
	
	/**
	 * Used for GA-internal statistical client function usage and error tracking,
	 * not implemented in php-ga as of now, but here for documentation completeness.
	 * @link http://glucik.blogspot.com/2011/02/utmu-google-analytics-request-parameter.html
	 * @var string
	 */
	public $utmu;
	
	
	// - - - - - - - - - - - - - - - - - Page parameters - - - - - - - - - - - - - - - - -
	
	/**
	 * Page request URI, e.g. "/path/page.html"
	 * @var string
	 */
	public $utmp;
	
	/**
	 * Page title
	 * @var string
	 */
	public $utmdt;
	
	/**
	 * Charset encoding (e.g. "UTF-8") or "-" as default
	 * @var string
	 */
	public $utmcs = '-';
	
	/**
	 * Referer URL, e.g. "http://www.example.com/path/page.html", "-" as default
	 * or "0" for internal referers
	 * @var string
	 */
	public $utmr = '-';
	
	
	// - - - - - - - - - - - - - - - - - Visitor parameters - - - - - - - - - - - - - - - - -
	
	/**
	 * IP Address of the end user, e.g. "123.123.123.123", found in GA for Mobile examples,
	 * but sadly seems to be ignored in normal GA use
	 * 
	 * @link http://github.com/mptre/php-ga/blob/master/ga.php
	 * @var string
	 */
	public $utmip;
	
	/**
	 * Visitor's locale string (all lower-case, country part optional), e.g. "de-de"
	 * @var string
	 */
	public $utmul;
	
	/**
	 * Visitor's Flash version, e.g. "9.0 r28" or "-" as default
	 * @var string
	 */
	public $utmfl = '-';
	
	/**
	 * Visitor's Java support, either 0 or 1 or "-" as default
	 * @var int|string
	 */
	public $utmje = '-';
	
	/**
	 * Visitor's screen color depth, e.g. "32-bit"
	 * @var string
	 */
	public $utmsc;
	
	/**
	 * Visitor's screen resolution, e.g. "1024x768"
	 * @var string
	 */
	public $utmsr;
	
    /**
	 * Visitor tracking cookie parameter.
	 * 
	 * This cookie is typically written to the browser upon the first visit to your site from that web browser.
	 * If the cookie has been deleted by the browser operator, and the browser subsequently visits your site,
	 * a new __utma cookie is written with a different unique ID.
	 * 
	 * This cookie is used to determine unique visitors to your site and it is updated with each page view.
	 * Additionally, this cookie is provided with a unique ID that Google Analytics uses to ensure both the
	 * validity and accessibility of the cookie as an extra security measure.
	 * 
	 * Expiration:
	 * 2 years from set/update.
	 * 
	 * Format:
	 * __utma=<domainHash>.<uniqueId>.<firstTime>.<lastTime>.<currentTime>.<sessionCount>
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/data/UTMA.as
	 * @var int
	 */
	public $__utma;
	
	
	// - - - - - - - - - - - - - - - - - Session parameters - - - - - - - - - - - - - - - - -
	
	/**
	 * Hit id for revenue per page tracking for AdSense, a random per-session ID
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/DocumentInfo.as#117
	 * @var int
	 */
	public $utmhid;
	
    /**
	 * Session timeout cookie parameter.
	 * Will never be sent with requests, but stays here for documentation completeness.
	 * 
	 * This cookie is used to establish and continue a user session with your site.
	 * When a user views a page on your site, the Google Analytics code attempts to update this cookie.
	 * If it does not find the cookie, a new one is written and a new session is established.
	 * 
	 * Each time a user visits a different page on your site, this cookie is updated to expire in 30 minutes,
	 * thus continuing a single session for as long as user activity continues within 30-minute intervals.
	 * 
	 * This cookie expires when a user pauses on a page on your site for longer than 30 minutes.
	 * You can modify the default length of a user session with the setSessionTimeout() method.
	 * 
	 * Expiration:
	 * 30 minutes from set/update.
	 * 
	 * Format:
	 * __utmb=<domainHash>.<trackCount>.<token>.<lastTime>
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/data/UTMB.as
	 * @var string
	 */
	public $__utmb;
	
    /**
	 * Session tracking cookie parameter.
	 * Will never be sent with requests, but stays here for documentation completeness.
	 * 
	 * This cookie operates in conjunction with the __utmb cookie to determine whether or not
	 * to establish a new session for the user.
	 * In particular, this cookie is not provided with an expiration date,
	 * so it expires when the user exits the browser.
	 * 
	 * Should a user visit your site, exit the browser and then return to your website within 30 minutes,
	 * the absence of the __utmc cookie indicates that a new session needs to be established,
	 * despite the fact that the __utmb cookie has not yet expired.
	 * 
	 * Expiration:
	 * Not set.
	 * 
	 * Format:
	 * __utmc=<domainHash>
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/data/UTMC.as
	 * @var string
	 */
	public $__utmc;
	
	
	// - - - - - - - - - - - - - - - - - E-Commerce parameters - - - - - - - - - - - - - - - - -
	
	/**
	 * Product Code. This is the sku code for a given product, e.g. "989898ajssi"
	 * @var string
	 */
	public $utmipc;
	
	/**
	 * Product Name, e.g. "T-Shirt"
	 * @var string
	 */
	public $utmipn;
	
	/**
	 * Unit Price. Value is set to numbers only, e.g. 19.95
	 * @var float
	 */
	public $utmipr;
	
	/**
	 * Unit Quantity, e.g. 4
	 * @var int
	 */
	public $utmiqt;
	
	/**
	 * Variations on an item, e.g. "white", "black", "green" etc.
	 * @var string
	 */
	public $utmiva;
	
	/**
	 * Order ID, e.g. "a2343898"
	 * @var string
	 */
	public $utmtid;
	
	/**
	 * Affiliation
	 * @var string
	 */
	public $utmtst;
	
	/**
	 * Total Cost, e.g. 20.00
	 * @var float
	 */
	public $utmtto;
	
	/**
	 * Tax Cost, e.g. 4.23
	 * @var float
	 */
	public $utmttx;
	
	/**
	 * Shipping Cost, e.g. 3.95
	 * @var float
	 */
	public $utmtsp;
	
	/**
	 * Billing City, e.g. "Cologne"
	 * @var string
	 */
	public $utmtci;
	
	/**
	 * Billing Region, e.g. "North Rhine-Westphalia"
	 * @var string
	 */
	public $utmtrg;
	
	/**
	 * Billing Country, e.g. "Germany"
	 * @var string
	 */
	public $utmtco;
	
	
	// - - - - - - - - - - - - - - - - - Campaign parameters - - - - - - - - - - - - - - - - -
	
	/**
	 * Starts a new campaign session. Either utmcn or utmcr is present on any given request,
	 * but never both at the same time. Changes the campaign tracking data; but does not start
	 * a new session. Either 1 or not set.
	 * 
	 * Found in gaforflash but not in ga.js, so we do not use it, but it will stay here for
	 * documentation completeness.
	 * 
	 * @deprecated
	 * @var int
	 */
	public $utmcn;
	
	/**
	 * Indicates a repeat campaign visit. This is set when any subsequent clicks occur on the
	 * same link. Either utmcn or utmcr is present on any given request, but never both at the
	 * same time. Either 1 or not set.
	 * 
	 * Found in gaforflash but not in ga.js, so we do not use it, but it will stay here for
	 * documentation completeness.
	 * 
	 * @deprecated
	 * @var int
	 */
	public $utmcr;
	
	/**
	 * Campaign ID, a.k.a. "utm_id" query parameter for ga.js
	 * @var string
	 */
	public $utmcid;
	
	/**
	 * Source, a.k.a. "utm_source" query parameter for ga.js
	 * @var string
	 */
	public $utmcsr;
	
	/**
	 * Google AdWords Click ID, a.k.a. "gclid" query parameter for ga.js
	 * @var string
	 */
	public $utmgclid;
	
	/**
	 * Not known for sure, but expected to be a DoubleClick Ad Click ID.
	 * @var string
	 */
	public $utmdclid;
	
	/**
	 * Name, a.k.a. "utm_campaign" query parameter for ga.js
	 * @var string
	 */
	public $utmccn;
	
	/**
	 * Medium, a.k.a. "utm_medium" query parameter for ga.js
	 * @var string
	 */
	public $utmcmd;
	
	/**
	 * Terms/Keywords, a.k.a. "utm_term" query parameter for ga.js
	 * @var string
	 */
	public $utmctr;
	
	/**
	 * Ad Content Description, a.k.a. "utm_content" query parameter for ga.js
	 * @var string
	 */
	public $utmcct;
	
	/**
	 * Unknown so far. Found in ga.js.
	 * @var int
	 */
	public $utmcvr;
	
    /**
	 * Campaign tracking cookie parameter.
	 * 
	 * This cookie stores the type of referral used by the visitor to reach your site,
	 * whether via a direct method, a referring link, a website search, or a campaign such as an ad or an email link.
	 * 
	 * It is used to calculate search engine traffic, ad campaigns and page navigation within your own site.
	 * The cookie is updated with each page view to your site.
	 * 
	 * Expiration:
	 * 6 months from set/update.
	 * 
	 * Format:
	 * __utmz=<domainHash>.<campaignCreation>.<campaignSessions>.<responseCount>.<campaignTracking>
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/data/UTMZ.as
	 * @var string
	 */
	public $__utmz;
	
	
	// - - - - - - - - - - - - - - - - - Social Tracking parameters - - - - - - - - - - - - - - - - -
	
	/**
	 * The network on which the action occurs (e.g. Facebook, Twitter).
	 * @var string
	 */
	public $utmsn;
	
	/**
	 * The type of action that happens (e.g. Like, Send, Tweet).
	 * @var string
	 */
	public $utmsa;
	
	/**
	 * The page URL from which the action occurred.
	 * @var string
	 */
	public $utmsid;
	
	
	// - - - - - - - - - - - - - - - - - Google Website Optimizer (GWO) parameters - - - - - - - - - - - - - - - - -
	
	// TODO: Implementation needed
    /**
     * Website Optimizer cookie parameter.
	 * 
	 * This cookie is used by Website Optimizer and only set when Website Optimizer is used in combination
	 * with GA. See the Google Website Optimizer Help Center for details.
     *
     * Expiration:
     * 2 years from set/update.
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/data/UTMX.as
	 * @var string
	 */
	public $__utmx;
	
	
	// - - - - - - - - - - - - - - - - - Custom Variables parameters (deprecated) - - - - - - - - - - - - - - - - -
	
	// TODO: Implementation needed?
	/**
	 * Deprecated custom variables cookie parameter.
	 * 
	 * This cookie parameter is no longer relevant as of migration from setVar() to
	 * setCustomVar() and hence not supported by this library, but will stay here for
	 * documentation completeness.
	 * 
	 * The __utmv cookie passes the information provided via the setVar() method,
	 * which you use to create a custom user segment.
	 * 
	 * Expiration:
	 * 2 years from set/update.
	 * 
	 * Format:
	 * __utmv=<domainHash>.<value>
	 * 
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/data/UTMV.as
	 * @deprecated
	 * @var string
	 */
	public $__utmv;
	
	
	/**
	 * Converts this parameter holder to a pure PHP array, filtering out all properties
	 * prefixed with an underscore ("_").
	 * 
	 * @return array
	 */
	public function toArray() {
		$array = array();
		foreach($this as $property => $value) {
			if($property[0] != '_') {
				$array[$property] = $value;
			}
		}
		return $array;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals\Request{

use UnitedPrototype\GoogleAnalytics\Event;

use UnitedPrototype\GoogleAnalytics\Internals\X10;

class EventRequest extends Request {
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\Event
	 */
	protected $event;
	
	
	/**
	 * @const int
	 */
	const X10_EVENT_PROJECT_ID = 5;
	
	
	/**
	 * @return string
	 */
	protected function getType() {
		return Request::TYPE_EVENT;
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/v4/Tracker.as#1503
	 * 
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildParameters() {
		$p = parent::buildParameters();
		
		$x10 = new X10();
		
		$x10->clearKey(self::X10_EVENT_PROJECT_ID);
		$x10->clearValue(self::X10_EVENT_PROJECT_ID);
		
		// Object / Category
		$x10->setKey(self::X10_EVENT_PROJECT_ID, X10::OBJECT_KEY_NUM, $this->event->getCategory());
		
		// Event Type / Action
		$x10->setKey(self::X10_EVENT_PROJECT_ID, X10::TYPE_KEY_NUM, $this->event->getAction());
		
		if($this->event->getLabel() !== null) {
			// Event Description / Label
			$x10->setKey(self::X10_EVENT_PROJECT_ID, X10::LABEL_KEY_NUM, $this->event->getLabel());
		}
		
		if($this->event->getValue() !== null) {
			$x10->setValue(self::X10_EVENT_PROJECT_ID, X10::VALUE_VALUE_NUM, $this->event->getValue());
		}
		
		$p->utme .= $x10->renderUrlString();
		
		if($this->event->getNoninteraction()) {
			$p->utmni = 1;
		}
		
		return $p;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Event
	 */
	public function getEvent() {
		return $this->event;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Event $event
	 */
	public function setEvent(Event $event) {
		$this->event = $event;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals\Request{

use UnitedPrototype\GoogleAnalytics\Config;

use UnitedPrototype\GoogleAnalytics\Internals\Util;

/**
 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/GIFRequest.as
 */
abstract class HttpRequest {	
	
	/**
	 * Indicates the type of request, will be mapped to "utmt" parameter
	 * 
	 * @see ParameterHolder::$utmt
	 * @var string
	 */
	protected $type;
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\Config
	 */
	protected $config;
	
	/**
	 * @var string
	 */
	protected $xForwardedFor;
	
	/**
	 * @var string
	 */
	protected $userAgent;
	
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Config $config
	 */
	public function __construct(Config $config = null) {
		$this->setConfig($config ? $config : new Config());
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Config
	 */
	public function getConfig() {
		return $this->config;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Config $config
	 */
	public function setConfig(Config $config) {
		$this->config = $config;
	}
	
	/**
	 * @param string $value
	 */
	protected function setXForwardedFor($value) {
		$this->xForwardedFor = $value;
	}
	
	/**
	 * @param string $value
	 */
	protected function setUserAgent($value) {
		$this->userAgent = $value;
	}
	
	/**
	 * @return string
	 */
	protected function buildHttpRequest() {
		$parameters = $this->buildParameters();
		
		// This constant is supported as the 4th argument of http_build_query()
		// from PHP 5.3.6 on and will tell it to use rawurlencode() instead of urlencode()
		// internally, see http://code.google.com/p/php-ga/issues/detail?id=3
		if(defined('PHP_QUERY_RFC3986')) {
			// http_build_query() does automatically skip all array entries
			// with null values, exactly what we want here
			$queryString = http_build_query($parameters->toArray(), '', '&', PHP_QUERY_RFC3986);
		} else {
			// Manually replace "+"s with "%20" for backwards-compatibility
			$queryString = str_replace('+', '%20', http_build_query($parameters->toArray(), '', '&'));
		}
		// Mimic Javascript's encodeURIComponent() encoding for the query
		// string just to be sure we are 100% consistent with GA's Javascript client
		$queryString = Util::convertToUriComponentEncoding($queryString);
		
		// Recent versions of ga.js use HTTP POST requests if the query string is too long
		$usePost = strlen($queryString) > 2036;
		
		if(!$usePost) {
			$r = 'GET ' . $this->config->getEndpointPath() . '?' . $queryString . ' HTTP/1.0' . "\r\n";
		} else {
			// FIXME: The "/p" shouldn't be hardcoded here, instead we need a GET and a POST endpoint...
			$r = 'POST /p' . $this->config->getEndpointPath() . ' HTTP/1.0' . "\r\n";
		}
		$r .= 'Host: ' . $this->config->getEndpointHost() . "\r\n";
		
		if($this->userAgent) {
			$r .= 'User-Agent: ' . str_replace(array("\n", "\r"), '', $this->userAgent) . "\r\n";
		}
		
		if($this->xForwardedFor) {
			// Sadly "X-Fowarded-For" is not supported by GA so far,
			// see e.g. http://www.google.com/support/forum/p/Google+Analytics/thread?tid=017691c9e71d4b24,
			// but we include it nonetheless for the pure sake of correctness (and hope)
			$r .= 'X-Forwarded-For: ' . str_replace(array("\n", "\r"), '', $this->xForwardedFor) . "\r\n";
		}
		
		if($usePost) {
			// Don't ask me why "text/plain", but ga.js says so :)
			$r .= 'Content-Type: text/plain' . "\r\n";
			$r .= 'Content-Length: ' . strlen($queryString) . "\r\n";
		}
		
		$r .= 'Connection: close' . "\r\n";
		$r .= "\r\n\r\n";
		
		if($usePost) {
			$r .= $queryString;
		}
		
		return $r;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected abstract function buildParameters();
	
	/**
	 * This method should only be called directly or indirectly by fire(), but must
	 * remain public as it can be called by a closure function.
	 * 
	 * Sends either a normal HTTP request with response or an asynchronous request
	 * to Google Analytics without waiting for the response. Will always return
	 * null in the latter case, or false if any connection problems arise.
	 * 
	 * @see HttpRequest::fire()
	 * @param string $request
	 * @return null|string|bool
	 */
	public function _send() {
		$request = $this->buildHttpRequest();
		$response = null;
		
		// Do not actually send the request if endpoint host is set to null
		if($this->config->getEndpointHost() !== null) {
			$timeout = $this->config->getRequestTimeout();
			
			$socket = fsockopen($this->config->getEndpointHost(), 80, $errno, $errstr, $timeout);
			if(!$socket) return false;
			
			if($this->config->getFireAndForget()) {
				stream_set_blocking($socket, false);
			}
			
			$timeoutS  = intval($timeout);
			$timeoutUs = ($timeout - $timeoutS) * 100000;
			stream_set_timeout($socket, $timeoutS, $timeoutUs);
			
			// Ensure that the full request is sent (see http://code.google.com/p/php-ga/issues/detail?id=11)
			$sentData = 0;
			$toBeSentData = strlen($request);
			while($sentData < $toBeSentData) {
				$sentData += fwrite($socket, $request);
			}
			
			if(!$this->config->getFireAndForget()) {
				while(!feof($socket)) {
					$response .= fgets($socket, 512);
				}
			}
			
			fclose($socket);
		}
		
		if($loggingCallback = $this->config->getLoggingCallback()) {
			$loggingCallback($request, $response);
		}
		
		return $response;
	}
	
	/**
	 * Simply delegates to send() if config option "sendOnShutdown" is disabled
	 * or enqueues the request by registering a PHP shutdown function.
	 */
	public function fire() {
		if($this->config->getSendOnShutdown()) {
			// This dumb variable assignment is needed as PHP prohibits using
			// $this in closure use statements
			$instance = $this;
			// We use a closure here to retain the current values/states of
			// this instance and $request (as the use statement will copy them
			// into its own scope)
			register_shutdown_function(function() use($instance) {
				$instance->_send();
			});
		} else {
			$this->_send();
		}
	}

}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals\Request{

use UnitedPrototype\GoogleAnalytics\Item;

use UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder;

class ItemRequest extends Request {
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\Item
	 */
	protected $item;
	
	
	/**
	 * @return string
	 */
	protected function getType() {
		return Request::TYPE_ITEM;
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/ecommerce/Item.as#61
	 * 
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildParameters() {
		$p = parent::buildParameters();		
		
		$p->utmtid = $this->item->getOrderId();
		$p->utmipc = $this->item->getSku();
		$p->utmipn = $this->item->getName();
		$p->utmiva = $this->item->getVariation();
		$p->utmipr = $this->item->getPrice();
		$p->utmiqt = $this->item->getQuantity();  
		
		return $p;
	}
	
	/**
	 * The GA Javascript client doesn't send any visitor information for
	 * e-commerce requests, so we don't either.
	 * 
	 * @param \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder $p
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildVisitorParameters(ParameterHolder $p) {
		return $p;
	}
	
	/**
	 * The GA Javascript client doesn't send any custom variables for
	 * e-commerce requests, so we don't either.
	 * 
	 * @param \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder $p
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildCustomVariablesParameter(ParameterHolder $p) {
		return $p;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Item
	 */
	public function getItem() {
		return $this->item;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Item $item
	 */
	public function setItem(Item $item) {
		$this->item = $item;
	}
	
}

}



/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals\Request{

use UnitedPrototype\GoogleAnalytics\Page;

use UnitedPrototype\GoogleAnalytics\Internals\X10;

class PageviewRequest extends Request {
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\Page
	 */
	protected $page;
	
	
	/**
	 * @const int
	 */
	const X10_SITESPEED_PROJECT_ID = 14;
	
	
	/**
	 * @return string
	 */
	protected function getType() {
		return Request::TYPE_PAGE;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildParameters() {
		$p = parent::buildParameters();
		
		$p->utmp  = $this->page->getPath();
		$p->utmdt = $this->page->getTitle();
		if($this->page->getCharset() !== null) {
			$p->utmcs = $this->page->getCharset();
		}
		if($this->page->getReferrer() !== null) {
			$p->utmr = $this->page->getReferrer();
		}
		
		if($this->page->getLoadTime() !== null) {
			// Sample sitespeed measurements
			if($p->utmn % 100 < $this->config->getSitespeedSampleRate()) {
				$x10 = new X10();
				
				$x10->clearKey(self::X10_SITESPEED_PROJECT_ID);
				$x10->clearValue(self::X10_SITESPEED_PROJECT_ID);
				
				// Taken from ga.js code
				$key = max(min(floor($this->page->getLoadTime() / 100), 5000), 0) * 100;
				$x10->setKey(self::X10_SITESPEED_PROJECT_ID, X10::OBJECT_KEY_NUM, $key);
				
				$x10->setValue(self::X10_SITESPEED_PROJECT_ID, X10::VALUE_VALUE_NUM, $this->page->getLoadTime());
				
				$p->utme .= $x10->renderUrlString();
			}
		}
		
		return $p;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Page
	 */
	public function getPage() {
		return $this->page;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Page $page
	 */
	public function setPage(Page $page) {
		$this->page = $page;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals\Request{

use UnitedPrototype\GoogleAnalytics\Tracker;
use UnitedPrototype\GoogleAnalytics\Visitor;
use UnitedPrototype\GoogleAnalytics\Session;
use UnitedPrototype\GoogleAnalytics\CustomVariable;

use UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder;
use UnitedPrototype\GoogleAnalytics\Internals\Util;
use UnitedPrototype\GoogleAnalytics\Internals\X10;

abstract class Request extends HttpRequest {
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\Tracker
	 */
	protected $tracker;
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\Visitor
	 */
	protected $visitor;
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\Session
	 */
	protected $session;
	
	
	/**
	 * @const string
	 */
	const TYPE_PAGE           = null;
	/**
	 * @const string
	 */
	const TYPE_EVENT          = 'event';
	/**
	 * @const string
	 */
	const TYPE_TRANSACTION    = 'tran';
	/**
	 * @const string
	 */
	const TYPE_ITEM           = 'item';
	/**
	 * @const string
	 */
	const TYPE_SOCIAL         = 'social';
	/**
	 * This type of request is deprecated in favor of encoding custom variables
	 * within the "utme" parameter, but we include it here for completeness
	 * 
	 * @see ParameterHolder::$__utmv
	 * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._setVar
	 * @deprecated
	 * @const string
	 */
	const TYPE_CUSTOMVARIABLE = 'var';
	
	/**
	 * @const int
	 */
	const X10_CUSTOMVAR_NAME_PROJECT_ID  = 8;
	/**
	 * @const int
	 */
	const X10_CUSTOMVAR_VALUE_PROJECT_ID = 9;
	/**
	 * @const int
	 */
	const X10_CUSTOMVAR_SCOPE_PROJECT_ID = 11;
	
	/**
	 * @const string
	 */
	const CAMPAIGN_DELIMITER = '|';
	
	
	/**
	 * Indicates the type of request, will be mapped to "utmt" parameter
	 * 
	 * @see ParameterHolder::$utmt
	 * @return string See Request::TYPE_* constants
	 */
	protected abstract function getType();
	
	/**
	 * @return string
	 */
	protected function buildHttpRequest() {
		$this->setXForwardedFor($this->visitor->getIpAddress());
		$this->setUserAgent($this->visitor->getUserAgent());
		
		// Increment session track counter for each request
		$this->session->increaseTrackCount();
		
		// See http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/v4/Configuration.as?r=237#48
		// and http://code.google.com/intl/de-DE/apis/analytics/docs/tracking/eventTrackerGuide.html#implementationConsiderations
		if($this->session->getTrackCount() > 500) {
			Tracker::_raiseError('Google Analytics does not guarantee to process more than 500 requests per session.', __METHOD__);
		}
		
		if($this->tracker->getCampaign()) {
			$this->tracker->getCampaign()->increaseResponseCount();
		}
		
		return parent::buildHttpRequest();
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildParameters() {
		$p = new ParameterHolder();
		
		$p->utmac = $this->tracker->getAccountId();
		$p->utmhn = $this->tracker->getDomainName();
		
		$p->utmt = $this->getType();
		$p->utmn = Util::generate32bitRandom();
		
		// The "utmip" parameter is only relevant if a mobile analytics
		// ID (MO-123456-7) was given,
		// see http://code.google.com/p/php-ga/issues/detail?id=9
		$p->utmip = $this->visitor->getIpAddress();
		
		$p->aip = $this->tracker->getConfig()->getAnonymizeIpAddresses() ? 1 : null;
		if($p->aip) {
			// Anonymize last IP block
			$p->utmip = substr($p->utmip, 0, strrpos($p->utmip, '.')) . '.0';
		}
		
		$p->utmhid = $this->session->getSessionId();
		$p->utms   = $this->session->getTrackCount();
		
		$p = $this->buildVisitorParameters($p);
		$p = $this->buildCustomVariablesParameter($p);
		$p = $this->buildCampaignParameters($p);
		$p = $this->buildCookieParameters($p);
		
		return $p;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder $p
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildVisitorParameters(ParameterHolder $p) {
		// Ensure correct locale format, see https://developer.mozilla.org/en/navigator.language
		$p->utmul = strtolower(str_replace('_', '-', $this->visitor->getLocale()));
		
		if($this->visitor->getFlashVersion() !== null) {
			$p->utmfl = $this->visitor->getFlashVersion();
		}
		if($this->visitor->getJavaEnabled() !== null) {
			$p->utmje = $this->visitor->getJavaEnabled();
		}
		if($this->visitor->getScreenColorDepth() !== null) {
			$p->utmsc = $this->visitor->getScreenColorDepth() . '-bit';
		}
		$p->utmsr = $this->visitor->getScreenResolution();
		
		return $p;
	}
	
	/**
	 * @link http://xahlee.org/js/google_analytics_tracker_2010-07-01_expanded.js line 575
	 * @param \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder $p
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildCustomVariablesParameter(ParameterHolder $p) {
		$customVars = $this->tracker->getCustomVariables();
		if($customVars) {
			if(count($customVars) > 5) {
				// See http://code.google.com/intl/de-DE/apis/analytics/docs/tracking/gaTrackingCustomVariables.html#usage
				Tracker::_raiseError('The sum of all custom variables cannot exceed 5 in any given request.', __METHOD__);
			}
			
			$x10 = new X10();
			
			$x10->clearKey(self::X10_CUSTOMVAR_NAME_PROJECT_ID);
			$x10->clearKey(self::X10_CUSTOMVAR_VALUE_PROJECT_ID);
			$x10->clearKey(self::X10_CUSTOMVAR_SCOPE_PROJECT_ID);
			
			foreach($customVars as $customVar) {
				// Name and value get encoded here,
				// see http://xahlee.org/js/google_analytics_tracker_2010-07-01_expanded.js line 563
				$name  = Util::encodeUriComponent($customVar->getName());
				$value = Util::encodeUriComponent($customVar->getValue());
				
				$x10->setKey(self::X10_CUSTOMVAR_NAME_PROJECT_ID, $customVar->getIndex(), $name);
				$x10->setKey(self::X10_CUSTOMVAR_VALUE_PROJECT_ID, $customVar->getIndex(), $value);
				if($customVar->getScope() !== null && $customVar->getScope() != CustomVariable::SCOPE_PAGE) {
					$x10->setKey(self::X10_CUSTOMVAR_SCOPE_PROJECT_ID, $customVar->getIndex(), $customVar->getScope());
				}
			}
			
			$p->utme .= $x10->renderUrlString();
		}
		
		return $p;
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/core/GIFRequest.as#123
	 * @param \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder $p
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildCookieParameters(ParameterHolder $p) {
		$domainHash = $this->generateDomainHash();
		
		$p->__utma  = $domainHash . '.';
		$p->__utma .= $this->visitor->getUniqueId() . '.';
		$p->__utma .= $this->visitor->getFirstVisitTime()->format('U') . '.';
		$p->__utma .= $this->visitor->getPreviousVisitTime()->format('U') . '.';
		$p->__utma .= $this->visitor->getCurrentVisitTime()->format('U') . '.';
		$p->__utma .= $this->visitor->getVisitCount();
		
		$p->__utmb  = $domainHash . '.';
		$p->__utmb .= $this->session->getTrackCount() . '.';
		// FIXME: What does "token" mean? I only encountered a value of 10 in my tests.
		$p->__utmb .= 10 . '.';
		$p->__utmb .= $this->session->getStartTime()->format('U');
		
		$p->__utmc = $domainHash;
		
		$cookies = array();
		$cookies[] = '__utma=' . $p->__utma . ';';
		if($p->__utmz) {
			$cookies[] = '__utmz=' . $p->__utmz . ';';
		}
		if($p->__utmv) {
			$cookies[] = '__utmv=' . $p->__utmv . ';';
		}
		
		$p->utmcc = implode('+', $cookies);
		
		return $p;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder $p
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildCampaignParameters(ParameterHolder $p) {
		$campaign = $this->tracker->getCampaign();
		if($campaign) {
			$p->__utmz  = $this->generateDomainHash() . '.';
			$p->__utmz .= $campaign->getCreationTime()->format('U') . '.';
			$p->__utmz .= $this->visitor->getVisitCount() . '.';
			$p->__utmz .= $campaign->getResponseCount() . '.';
			
			// See http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/campaign/CampaignTracker.as#236
			$data = array(
				'utmcid'   => $campaign->getId(),
				'utmcsr'   => $campaign->getSource(),
				'utmgclid' => $campaign->getGClickId(),
				'utmdclid' => $campaign->getDClickId(),
				'utmccn'   => $campaign->getName(),
				'utmcmd'   => $campaign->getMedium(),
				'utmctr'   => $campaign->getTerm(),
				'utmcct'   => $campaign->getContent(),
			);
			foreach($data as $key => $value) {
				if($value !== null && $value !== '') {
					// Only spaces and pluses get escaped in gaforflash and ga.js, so we do the same
					$p->__utmz .= $key . '=' . str_replace(array('+', ' '), '%20', $value) . static::CAMPAIGN_DELIMITER;
				}
			}
			$p->__utmz = rtrim($p->__utmz, static::CAMPAIGN_DELIMITER);
		}
		
		return $p;
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/v4/Tracker.as#585
	 * @return string
	 */
	protected function generateDomainHash() {
		$hash = 1;
		
		if($this->tracker->getAllowHash()) {
			$hash = Util::generateHash($this->tracker->getDomainName());
		}
		
		return $hash;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Tracker
	 */
	public function getTracker() {
		return $this->tracker;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Tracker $tracker
	 */
	public function setTracker(Tracker $tracker) {
		$this->tracker = $tracker;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Visitor
	 */
	public function getVisitor() {
		return $this->visitor;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Visitor $visitor
	 */
	public function setVisitor(Visitor $visitor) {
		$this->visitor = $visitor;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Session
	 */
	public function getSession() {
		return $this->session;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Session $session
	 */
	public function setSession(Session $session) {
		$this->session = $session;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals\Request{

use UnitedPrototype\GoogleAnalytics\SocialInteraction;

class SocialinteractionRequest extends PageviewRequest {
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\SocialInteraction
	 */
	protected $socialInteraction;
	
	
	/**
	 * @return string
	 */
	protected function getType() {
		return Request::TYPE_SOCIAL;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildParameters() {
		$p = parent::buildParameters();
		
		$p->utmsn  = $this->socialInteraction->getNetwork();
		$p->utmsa  = $this->socialInteraction->getAction();
		$p->utmsid = $this->socialInteraction->getTarget();
		if($p->utmsid === null) {
			// Default to page path like ga.js,
			// see http://code.google.com/apis/analytics/docs/tracking/gaTrackingSocial.html#settingUp
			$p->utmsid = $this->page->getPath();
		}
		
		return $p;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\SocialInteraction
	 */
	public function getSocialInteraction() {
		return $this->socialInteraction;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\SocialInteraction $socialInteraction
	 */
	public function setSocialInteraction(SocialInteraction $socialInteraction) {
		$this->socialInteraction = $socialInteraction;
	}
	
}

}




/**
 * Generic Server-Side Google Analytics PHP Client
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License (LGPL) as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA.
 * 
 * Google Analytics is a registered trademark of Google Inc.
 * 
 * @link      http://code.google.com/p/php-ga
 * 
 * @license   http://www.gnu.org/licenses/lgpl.html
 * @author    Thomas Bachem <tb@unitedprototype.com>
 * @copyright Copyright (c) 2010 United Prototype GmbH (http://unitedprototype.com)
 */

namespace UnitedPrototype\GoogleAnalytics\Internals\Request{

use UnitedPrototype\GoogleAnalytics\Transaction;

use UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder;

class TransactionRequest extends Request {
	
	/**
	 * @var \UnitedPrototype\GoogleAnalytics\Transaction
	 */
	protected $transaction;
	
	
	/**
	 * @return string
	 */
	protected function getType() {
		return Request::TYPE_TRANSACTION;
	}
	
	/**
	 * @link http://code.google.com/p/gaforflash/source/browse/trunk/src/com/google/analytics/ecommerce/Transaction.as#76
	 * 
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildParameters() {
		$p = parent::buildParameters();
		
		$p->utmtid = $this->transaction->getOrderId();
		$p->utmtst = $this->transaction->getAffiliation();
		$p->utmtto = $this->transaction->getTotal();
		$p->utmttx = $this->transaction->getTax();
		$p->utmtsp = $this->transaction->getShipping();
		$p->utmtci = $this->transaction->getCity();
		$p->utmtrg = $this->transaction->getRegion();
		$p->utmtco = $this->transaction->getCountry();
		
		return $p;
	}
	
	/**
	 * The GA Javascript client doesn't send any visitor information for
	 * e-commerce requests, so we don't either.
	 * 
	 * @param \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder $p
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildVisitorParameters(ParameterHolder $p) {
		return $p;
	}
	
	/**
	 * The GA Javascript client doesn't send any custom variables for
	 * e-commerce requests, so we don't either.
	 * 
	 * @param \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder $p
	 * @return \UnitedPrototype\GoogleAnalytics\Internals\ParameterHolder
	 */
	protected function buildCustomVariablesParameter(ParameterHolder $p) {
		return $p;
	}
	
	/**
	 * @return \UnitedPrototype\GoogleAnalytics\Transaction
	 */
	public function getTransaction() {
		return $this->transaction;
	}
	
	/**
	 * @param \UnitedPrototype\GoogleAnalytics\Transaction $transaction
	 */
	public function setTransaction(Transaction $transaction) {
		$this->transaction = $transaction;
	}
	
}

}
?>