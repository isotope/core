<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Response;

use Isotope\Frontend;

class Response
{
    /**
     * Headers
     * @var array
     */
    protected $arrHeaders = array();

    /**
     * Content
     * @var string
     */
    protected $strContent = '';

    /**
     * HTTP Status code
     * @var integer
     */
    protected $intStatus;

    /**
     * Status codes translation table.
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2012-02-13).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     * @var array
     */
    public static $arrStatuses = array
    (
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC-reschke-http-status-308-07
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    );

    /**
     * Creates a new HTTP response
     * @param   string The response content
     * @param   integer The response HTTP status code
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     */
    public function __construct($strContent = '', $intStatus = 200)
    {
        $this->intStatus  = (int) $intStatus;
        $this->strContent = $strContent;

        if (!in_array($this->intStatus, array_keys(self::$arrStatuses))) {
            throw new \InvalidArgumentException('The status code "' . $this->intStatus . '" is invalid!');
        }

        // Set default Content-Type
        $this->setHeader('Content-Type', 'text-plain');
    }

    /**
     * Sets a header
     * @param   string The header name
     * @param   string The header content
     * @return  Response
     */
    public function setHeader($strName, $strContent)
    {
        $this->arrHeaders[$strName] = $strContent;

        return $this;
    }

    /**
     * Remove a header
     * @param   string The header name
     * @return  Response
     */
    public function removeHeader($strName)
    {
        unset($this->arrHeaders[$strName]);

        return $this;
    }

    /**
     * Get a header
     * @param   string The header name
     * @return  string The header content
     */
    public function getHeader($strName)
    {
        return $this->arrHeaders[$strName];
    }

    /**
     * Send the response
     * @param   boolean Exit script
     * @return  Response|null
     */
    public function send($blnExit = true)
    {
        // Clean the output buffer
        ob_end_clean();

        // Replace Isotope tags
        $this->strContent = Frontend::replaceTags($this->strContent);

        // Content-Length
        $this->setHeader('Content-Length', strlen($this->strContent));

        // Fix charset
        $strContentType = $this->getHeader('Content-Type');
        if (strpos($strContentType, 'charset') !== false) {
            $this->setHeader('Content-Type', $strContentType . '; charset=' . $GLOBALS['TL_CONFIG']['characterSet']);
        }

        // Send
        $this->sendHeaders();
        echo $this->strContent;

        if ($blnExit) {
            exit;
        }

        return $this;
    }

    /**
     * Sends the HTTP headers
     */
    protected function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }

        // Status
        $strVersion = ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0') ? '1.0' : '1.1';
        header(sprintf('HTTP/%s %s %s', $strVersion, $this->intStatus, self::$arrStatuses[$this->intStatus]));

        // Headers
        foreach ($this->arrHeaders as $strName => $strContent) {
            header($strName . ': ' . $strContent);
        }
    }
}