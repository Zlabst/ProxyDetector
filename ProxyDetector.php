<?php
/**
 * Created by PhpStorm.
 * User: fabian.lenczewski
 * Date: 2015-11-02
 * Time: 22:47
 */
namespace ProxyDetector;

class ProxyDetector
{
    /**
     * IP address to verify
     * @var null
     */
    private $_ip = null;

    /**
     * Potential hostname string
     * @var array
     */
    public $proxyHostnameString = ['proxy', 'proxi'];

    /**
     * Message list from checker
     * @var array
     */
    private $_message = [];

    const CODE_HOSTNAME  = 1;
    const CODE_PROXYLIST = 2;
    const CODE_TOR       = 3;

    /**
     * ProxyDetector constructor.
     * @param string $ip - IP address to verify
     */
    public function __construct($ip = null)
    {
        if(null !== $ip) {
            $this->setIp($ip);
        }
    }

    /**
     * Ip setter
     *
     * @param string $ip - IP address to verify
     * @return mixed
     */
    public function setIp($ip) {

        if(!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('This is not a valid IP address ('. $ip .').');
        }

        return $this->_ip = $ip;
    }

    /**
     * Get Ip address
     *
     * @return string
     */
    public function getIp()
    {
        return $this->_ip;
    }

    /**
     * Check IP address
     *
     * @return bool
     */
    public function isProxy()
    {
        $this->checkHostname();
        $this->checkProxyList();
        // @todo: TOR exit nodes

        return count($this->_message) > 0 ? true : false;
    }


    /**
     * Check proxy string in hostname
     *
     * @param null $hostname
     * @throws Exception
     */
    public function checkHostname($hostname = null) {

        if(null === $hostname) {
            $hostname = gethostbyaddr($this->getIp());
        }

        foreach($this->proxyHostnameString as $proxyString) {
            if( false !== strripos($hostname, $proxyString) ) {
                $this->_setMessage(self::CODE_HOSTNAME, 'Proxy founded. Hostname: '. $hostname);
                break;
            }
        }
    }

    /**
     * Search ip address in proxy file list
     */
    public function checkProxyList()
    {
        $proxyFile = __DIR__ .'/data/proxy-list.txt';
        $proxyList = file($proxyFile);

        foreach($proxyList as $proxyIp) {
            list($ip) = explode(':', trim($proxyIp));
            if($ip === $this->getIp()) {
                $this->_setMessage(self::CODE_PROXYLIST, 'Proxy founded in proxy list: '. $proxyIp);
                break;
            }
        }
    }

    /**
     * setter for chcecker message
     *
     * @param int $code - method code (hostname, proxylist, tor)
     * @param string $message -
     */
    private function _setMessage($code, $message)
    {
        $this->_message[$code] = $message;
    }

    /**
     * Get all messages
     *
     * @return array message list
     */
    public function getMessageList()
    {
        return $this->_message;
    }
}