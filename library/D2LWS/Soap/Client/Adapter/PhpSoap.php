<?php
/**
 * CdliD2LSoap - PHP5 Wrapper for Desire2Learn Web Services
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/adamlundrigan/zfD2L/blob/master/LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to adamlundrigan@cdli.ca so we can send you a copy immediately.
 * 
 * @copyright  Copyright (c) 2011 Centre for Distance Learning and Innovation
 * @license    New BSD License 
 * @author     Adam Lundrigan <adamlundrigan@cdli.ca>
 * @author     Thomas Hawkins <thawkins@mun.ca>
 */

/**
 * SOAP Client
 */
class D2LWS_Soap_Client_Adapter_PhpSoap implements D2LWS_Soap_Client
{
    /**
     * D2LWS Instance
     * @type D2LWS_Instance
     */
    protected $_instance = NULL;

    protected $_soap = NULL;

    protected $_wsdl = NULL;

    protected $_options = NULL;
    
    /**
     * Default constructor for SOAP client
     * @param $wsdl string - WSDL file
     * @param $options array - Options
     */
    public function __construct($wsdl = NULL, $options = NULL)
    {
        $this->setWsdl($wsdl);
        $this->setOptions($options);
    }
    

    /**
     * Invoke method on remote server
     * @param string $method
     * @param array $args
     * @return mixed Result of remote call
     */
    public function __call($method, $args)
    {
        $result = null;
        $i = $this->getInstance();
        $soap = $this->getSoapClientInstance();
       
        /*
         * Check and see if $request first element is single entity (ns2:)
         * or an array or OrgUnitId or UserId (ns1:)
         */

        $NS = @( is_array($args[0]) && count($args[0]) > 0 && is_array($args[0][array_shift(@array_keys($args[0]))]) ? 'ns1' : 'ns2' );
        
        // TODO: Investigate why this is necessary
        // Will get exception if namespace not forced in this way
        // D2L.WS.Implementation.RequestHeaderValidationFailedException: Request header is required!
        if ( in_array($method, array('CreateGroup','CreateSection')) )
        {
            $NS = 'ns3';
        }
        elseif ( in_array($method, array('UpdateGroup', 'UpdateGroupType', 'UpdateSection','UpdateSemester')) )
        {
            $NS = 'ns1';
        }
        
        $rawheaders = "<{$NS}:RequestHeader>";
        $rawheaders .= "<{$NS}:Version>1.0</{$NS}:Version><{$NS}:CorellationId>123456</{$NS}:CorellationId>";
        if ( !in_array(strtolower($method), array('authenticate','authenticate2')) )
        {
            $rawheaders .= "<{$NS}:AuthenticationToken>";
            $rawheaders .= $i->getAuthToken();
            $rawheaders .= "</{$NS}:AuthenticationToken>";
        }
        $rawheaders .= "</{$NS}:RequestHeader>";
        $innerHeader = new SoapVar($rawheaders,XSD_ANYXML);
        $metaheader = new SoapHeader($i->getConfig('webservice.common.namespace'),'RequestHeader',$innerHeader);//$headers);
        $soap->__setSoapHeaders($metaheader);
        
        try {
            $result = $soap->__soapCall($method, $args, $this->getOptions());
        }
        catch ( SoapFault $ex )
        {
            if ( isset($ex->faultstring) ) {
                throw new D2LWS_Soap_Client_Exception($ex->faultstring);
            }
            // If a header wasn't returned, re-throw the exception message
            try 
            {
                $xml = new SimpleXMLElement($soap->__getLastResponse());
                if ( @array_shift($xml->xpath('/soap:Envelope/soap:Header')) == NULL )
                {
                    throw new D2LWS_Soap_Client_Exception($ex->getMessage());
                }
            }
            catch ( Exception $ex )
            {
                throw new D2LWS_Soap_Client_Exception($ex->getMessage());
            }
        }

        try 
        {
            $xml = new SimpleXMLElement($soap->__getLastResponse());
            $header = @array_shift(@$xml->xpath('/soap:Envelope/soap:Header'));
            if ( $header != NULL && $header->ResponseHeader->Status->Code != 'Success') 
            {
                if ( isset($header->ResponseHeader->Status->SystemErrors->SystemErrorInfo->Message) )
                {
                    throw new D2LWS_Soap_Client_Exception($header->ResponseHeader->Status->SystemErrors->SystemErrorInfo->Message);
                }
                elseif ( isset($header->ResponseHeader->Status->Code) )
                {
                    $exbody = @array_shift($xml->xpath('/soap:Envelope/soap:Body'));
                    $opname = $header->ResponseHeader->OperationName;
                    $respname = preg_replace("/Request$/", "Response", $opname);

                    switch ( $header->ResponseHeader->Status->Code )
                    {
                        case 'BusinessRuleFailure':
                        {
                            $extype = @$exbody->$respname->BusinessErrors->BusinessErrorInfo->ErrorType;
                            $exmessage = @$exbody->$respname->BusinessErrors->BusinessErrorInfo->Message;
                            break;
                        }
                        default:
                        {
                            $extype = "Unknown";
                            $exmessage = "Unknown Error Type Returned!";
                            break;
                        }
                    }

                    throw new D2LWS_Soap_Client_Exception("{$extype}: {$exmessage}");

                }
            }
        }
        catch ( Exception $ex ) 
        {
            throw new D2LWS_Soap_Client_Exception($ex->getMessage());
        }

        return $result;
    }
    
    /**
     * Set D2LWS instance
     * @param $inst Instance to assign
     * @return $this
     */
    public function setInstance(D2LWS_Instance $inst)
    {
        $this->_instance = $inst;
        return $this;
    }
    
    /**
     * Get D2LWS instance
     * @return D2LWS instance
     */
    public function getInstance() { return $this->_instance; }

    public function setLocation($loc)
    {
        $this->_options['location'] = $loc;
        return $this;
    }
    public function getLocation()
    {
        return $this->_options['location'];
    }

    public function setWsdl($wsdl)
    {
        $this->_wsdl = $wsdl;
        return $this;
    }

    public function getWsdl()
    {
        return $this->_wsdl;
    }

    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    protected function getSoapClientInstance()
    {
        if (is_null($this->_soap)) {
            $this->_soap = new SoapClient(
                $this->getWsdl(),
                $this->getOptions()
            );
        }
        return $this->_soap;
    }

}
