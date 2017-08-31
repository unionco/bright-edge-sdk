<?php
use PHPUnit\Framework\TestCase;

/**
c:\wamp64\bin\php\php7.0.10\php.exe c:\php56\phpunit.phar --bootstrap be_ixf_client.php tests\BEIXFClientTestCase.php
 */

/**
 * @covers BEIXFClient
 */
final class BEIXFClientTestCase extends TestCase {
    public function testGetSignedNumber() {
        $this->assertEquals(
            5,
            IXFSDKUtils::getSignedNumber(5)
        );
        $this->assertEquals(
            -5,
            IXFSDKUtils::getSignedNumber(-5)
        );
        $this->assertEquals(
            313923,
            IXFSDKUtils::getSignedNumber(313923)
        );
        $this->assertEquals(
            -313923,
            IXFSDKUtils::getSignedNumber(-313923)
        );
        $this->assertEquals(
            341720826,
            IXFSDKUtils::getSignedNumber(2139235434234)
        );

    }

    public function testGetPageHash() {
        $this->assertEquals(
            "02026868259",
            IXFSDKUtils::getPageHash("/test/index.jsp")
        );
    }

    public function testOverrideHostInURL() {
        $this->assertEquals(
            "http://cnn.com/topnews",
            IXFSDKUtils::overrideHostInURL("http://www.abc.com/topnews", "cnn.com")
        );
        $this->assertEquals(
            "http://cnn.com/topnews",
            IXFSDKUtils::overrideHostInURL("http://www.abc.com/topnews", "cnn.com:80")
        );
        $this->assertEquals(
            "http://cnn.com:81/topnews",
            IXFSDKUtils::overrideHostInURL("http://www.abc.com/topnews", "cnn.com:81")
        );
    }

    public function testNormalizeURL() {
        $whitelistParameters = array();

        // make sure we remove all query string by default
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        // make sure we remove extraneous port info
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com:80/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );
        $this->assertEquals(
            "http://www.brightedge.com:81/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com:81/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );
        $this->assertEquals(
            "http://www.brightedge.com:81/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com:81/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );
        $this->assertEquals(
            "https://www.brightedge.com/test/index.jsp",
            IXFSDKUtils::normalizeURL("https://www.brightedge.com:443/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );
        $this->assertEquals(
            "https://www.brightedge.com:444/test/index.jsp",
            IXFSDKUtils::normalizeURL("https://www.brightedge.com:444/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        // make sure whitelist parameter works
        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?k1=v1",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        array_push($whitelistParameters, "k2");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        $whitelistParameters = array();
        array_push($whitelistParameters, "k2");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?k2=v2",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        // single key multiple value
        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?k1=v1&k1=v2",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k1=v2", $whitelistParameters)
        );

        // make sure we keep the encoding value
        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?k1=%25abcdef%3D",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=%25abcdef%3D&k2=v2", $whitelistParameters)
        );

        // check sorting in key
        $whitelistParameters = array();
        array_push($whitelistParameters, "ka");
        array_push($whitelistParameters, "kb");
        array_push($whitelistParameters, "kc");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?ka=v2&kb=v1&kc=v3",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?kb=v1&kc=v3&ka=v2", $whitelistParameters)
        );

        // check sorting in key with single key multiple values
        // seems like comparator keeps position
        $whitelistParameters = array();
        array_push($whitelistParameters, "ka");
        array_push($whitelistParameters, "kb");
        array_push($whitelistParameters, "kc");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?ka=v2.0&ka=v2.1&kb=v1&kc=v3",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?kb=v1&ka=v2.0&kc=v3&ka=v2.1", $whitelistParameters)
        );
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?ka=v2.1&ka=v2.0&kb=v1&kc=v3",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?kb=v1&ka=v2.1&kc=v3&ka=v2.0", $whitelistParameters)
        );

    }

    public function testUserAgentMatchesRegex() {
        $userAgentRegex1 = "google|bingbot|msnbot|slurp|duckduckbot|baiduspider|yandexbot|sogou|exabot|facebot|ia_archiver";
        $userAgentRegex2 = "chrome|google|bingbot|msnbot|slurp|duckduckbot|baiduspider|yandexbot|sogou|exabot|facebot|ia_archiver";
        $this->assertFalse(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36",
                $userAgentRegex2));

        // Google Crawlers: https://support.google.com/webmasters/answer/1061943?hl=en
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Googlebot/2.1 (+http://www.google.com/bot.html)",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mediapartners-Google",
                $userAgentRegex1));

        // Bing Crawlers: https://www.bing.com/webmaster/help/which-crawlers-does-bing-use-8c184ec0
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("msnbot/2.0b (+http://search.msn.com/msnbot.htm)",
                $userAgentRegex1));
    }

    public function testconvertToNormalizedTimeZone() {
        // daylight savings 3/12/2017-11/5/2017
        // not supported by PHP
        $epochTimeMillis = 1504199514000;
        $this->assertEquals("p_tstr:Thu Aug 31 10:11:54 PST 2017; p_epoch:1504199514000",
            IXFSDKUtils::convertToNormalizedTimeZone($epochTimeMillis, "p"));

        // standard
        $epochTimeMillis = 1490980314000;
        $this->assertEquals("p_tstr:Fri Mar 31 10:11:54 PST 2017; p_epoch:1490980314000",
            IXFSDKUtils::convertToNormalizedTimeZone($epochTimeMillis, "p"));

        // test single digit month, day, hour, and minute
        $epochTimeMillis = 1488388194000;
        $this->assertEquals("p_tstr:Wed Mar 01 09:09:54 PST 2017; p_epoch:1488388194000",
            IXFSDKUtils::convertToNormalizedTimeZone($epochTimeMillis, "p"));

    }

    public function testConvertToNormalizedGoogleIndexTimeZone() {
        // daylight savings 3/12/2017-11/5/2017
        $epochTimeMillis = 1504199514000;
        $this->assertEquals("py_2017; pm_08; pd_31; ph_10; pmh_11; p_epoch:1504199514000",
            IXFSDKUtils::convertToNormalizedGoogleIndexTimeZone($epochTimeMillis, "p"));

        // standard
        $epochTimeMillis = 1488388314000;
        $this->assertEquals("py_2017; pm_03; pd_01; ph_09; pmh_11; p_epoch:1488388314000",
            IXFSDKUtils::convertToNormalizedGoogleIndexTimeZone($epochTimeMillis, "p"));

        // test single digit month, day, hour, and minute
        $epochTimeMillis = 1488388194000;
        $this->assertEquals("py_2017; pm_03; pd_01; ph_09; pmh_09; p_epoch:1488388194000",
            IXFSDKUtils::convertToNormalizedGoogleIndexTimeZone($epochTimeMillis, "p"));

    }

}
?>
