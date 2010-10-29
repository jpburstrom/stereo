<?php
require_once 'Soundcloud_Test_Helper.php';

class Soundcloud_Test extends PHPUnit_Framework_TestCase {

    protected $soundcloud;

    function setUp() {
        $this->soundcloud = new Soundcloud_Expose(
            '1337',
            '1337',
            'http://soundcloud.local/callback'
        );
    }

    function tearDown() {
        $this->soundcloud = null;
    }

    /**
     * @dataProvider dataProviderVersion
     */
    function testVersionFormat($regex) {
        $this->assertTrue(
            (bool)preg_match($regex, Soundcloud_Version::get())
        );
    }

    /**
     * @dataProvider dataProviderUserAgent
     */
    function testGetUserAgent($regex) {
        $this->assertTrue(
            (bool)preg_match($regex, $this->soundcloud->getUserAgent())
        );
    }

    function testApiVersion() {
        $this->assertEquals(1, $this->soundcloud->getApiVersion());
    }

    function testGetAuthorizeUrl() {
        $this->assertEquals(
            'https://soundcloud.com/connect?client_id=1337&redirect_uri=http%3A%2F%2Fsoundcloud.local%2Fcallback&response_type=code',
            $this->soundcloud->getAuthorizeUrl()
        );
    }

    function testGetAuthorizeUrlWithCustomQueryParameters() {
        $this->assertEquals(
            'https://soundcloud.com/connect?client_id=1337&redirect_uri=http%3A%2F%2Fsoundcloud.local%2Fcallback&response_type=code&foo=bar',
            $this->soundcloud->getAuthorizeUrl(array('foo' => 'bar'))
        );

        $this->assertEquals(
            'https://soundcloud.com/connect?client_id=1337&redirect_uri=http%3A%2F%2Fsoundcloud.local%2Fcallback&response_type=code&foo=bar&bar=foo',
            $this->soundcloud->getAuthorizeUrl(array('foo' => 'bar', 'bar' => 'foo'))
        );
    }

    function testGetAccessTokenUrl() {
        $this->assertEquals(
            'https://api.soundcloud.com/oauth2/token',
            $this->soundcloud->getAccessTokenUrl()
        );
    }

    function testSetAccessToken() {
        $this->soundcloud->setAccessToken('1337');

        $this->assertEquals('1337', $this->soundcloud->getAccessToken());
    }

    function testSetDevelopment() {
        $this->soundcloud->setDevelopment(true);

        $this->assertTrue($this->soundcloud->getDevelopment());
    }

    function testSetRedirectUri() {
        $this->soundcloud->setRedirectUri('http://soundcloud.local/callback');

        $this->assertEquals(
            'http://soundcloud.local/callback',
            $this->soundcloud->getRedirectUri()
        );
    }

    function testDefaultResponseFormat() {
        $this->assertEquals(
            'application/json',
            $this->soundcloud->getResponseFormat()
        );
    }

    function testSetResponseFormatHtml() {
        $this->setExpectedException('Soundcloud_Invalid_Response_Format_Exception');

        $this->soundcloud->setResponseFormat('html');
    }

    function testSetResponseFormatJson() {
        $this->soundcloud->setResponseFormat('json');

        $this->assertEquals(
            'application/json',
            $this->soundcloud->getResponseFormat()
        );
    }

    function testSetResponseFormatXml() {
        $this->soundcloud->setResponseFormat('xml');

        $this->assertEquals(
            'application/xml',
            $this->soundcloud->getResponseFormat()
        );
    }

    function testResponseCodeSuccess() {
        $this->assertTrue($this->soundcloud->validResponseCode(200));
    }

    function testResponseCodeRedirect() {
        $this->assertFalse($this->soundcloud->validResponseCode(301));
    }

    function testResponseCodeClientError() {
        $this->assertFalse($this->soundcloud->validResponseCode(400));
    }

    function testResponseCodeServerError() {
        $this->assertFalse($this->soundcloud->validResponseCode(500));
    }

    function testBuildDefaultHeaders() {
        $this->assertEquals(
            array('Accept: application/json'),
            $this->soundcloud->buildDefaultHeaders()
        );
    }

    function testBuildDefaultHeadersWithAccessToken() {
        $this->soundcloud->setAccessToken('1337');

        $this->assertEquals(
            array('Accept: application/json', 'Authorization: OAuth 1337'),
            $this->soundcloud->buildDefaultHeaders()
        );
    }

    function testBuildUrl() {
        $this->assertEquals(
            'https://api.soundcloud.com/v1/me',
            $this->soundcloud->buildUrl('me')
        );
    }

    function testBuildUrlWithQueryParameters() {
        $this->assertEquals(
            'https://api.soundcloud.com/v1/tracks?q=rofl+dubstep',
            $this->soundcloud->buildUrl(
                'tracks',
                array('q' => 'rofl dubstep')
            )
        );

        $this->assertEquals(
            'https://api.soundcloud.com/v1/tracks?q=rofl+dubstep&filter=public',
            $this->soundcloud->buildUrl(
                'tracks',
                array('q' => 'rofl dubstep', 'filter' => 'public')
            )
        );
    }

    function testBuildUrlWithDevelopmentDomain() {
        $this->soundcloud->setDevelopment(true);

        $this->assertEquals(
            'https://api.sandbox-soundcloud.com/v1/me',
            $this->soundcloud->buildUrl('me')
        );
    }

    function testBuildUrlWithoutApiVersion() {
        $this->assertEquals(
            'https://api.soundcloud.com/me',
            $this->soundcloud->buildUrl('me', null, false)
        );
    }

    function testBuildUrlWithAbsoluteUrl() {
        $this->assertEquals(
            'https://api.soundcloud.com/me',
            $this->soundcloud->buildUrl('https://api.soundcloud.com/me')
        );
    }

    function testSoundcloudMissingConsumerKeyException() {
        $this->setExpectedException('Soundcloud_Missing_Client_Id_Exception');

        $soundcloud = new Soundcloud('', '');
    }

    function testSoundcloudInvalidHttpResponseCodeException() {
        $this->setExpectedException('Soundcloud_Invalid_Http_Response_Code_Exception');

        $this->soundcloud->get('me');
    }

    function testSoundcloudInvalidHttpResponseCodeGetHttpBody() {
        try {
            $this->soundcloud->get('me');
        } catch (Soundcloud_Invalid_Http_Response_Code_Exception $e) {
            $this->assertEquals(
                '{"error":"401 - Unauthorized"}',
                $e->getHttpBody()
            );
        }
    }

    function testSoundcloudInvalidHttpResponseCodeGetHttpCode() {
        try {
            $this->soundcloud->get('me');
        } catch (Soundcloud_Invalid_Http_Response_Code_Exception $e) {
            $this->assertEquals(
                401,
                $e->getHttpCode()
            );
        }
    }

    static function dataProviderVersion() {
        return array(array('/^[0-9]+\.[0-9]+\.[0-9]+(beta[0-9]+)?$/'));
    }

    static function dataProviderUserAgent() {
        return array(
            array(
                '/^PHP\-SoundCloud\/[0-9]+\.[0-9]+\.[0-9]+(beta[0-9]+)?$/'
            )
        );
    }

}
