<?php


namespace Paul\Links;


class UserAgentParser
{
    const PLATFORM = 'platform';
    const BROWSER = 'browser';
    const BROWSER_VERSION = 'version';

    /**
     * Parse user agent data
     *
     * @param null $u_agent
     * @return array|null[]
     */
    public static function parseUserAgent($u_agent = null)
    {
        if ($u_agent === null && isset($_SERVER['HTTP_USER_AGENT'])) {
            $u_agent = (string)$_SERVER['HTTP_USER_AGENT'];
        }

        if ($u_agent === null) {
            throw new \InvalidArgumentException('parse_user_agent requires a user agent');
        }

        $platform = null;
        $browser = null;
        $version = null;

        $empty = array(self::PLATFORM => $platform, self::BROWSER => $browser, self::BROWSER_VERSION => $version);

        if (!$u_agent) {
            return $empty;
        }

        if (preg_match('/\((.*?)\)/m', $u_agent, $parent_matches)) {
            preg_match_all(<<<'REGEX'
/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|(Open|Net|Free)BSD|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS|Switch)|Xbox(\ One)?)
(?:\ [^;]*)?
(?:;|$)/imx
REGEX
                , $parent_matches[1], $result);

            $priority = array(
                'Xbox One',
                'Xbox',
                'Windows Phone',
                'Tizen',
                'Android',
                'FreeBSD',
                'NetBSD',
                'OpenBSD',
                'CrOS',
                'X11'
            );

            $result[self::PLATFORM] = array_unique($result[self::PLATFORM]);
            if (count($result[self::PLATFORM]) > 1) {
                if ($keys = array_intersect($priority, $result[self::PLATFORM])) {
                    $platform = reset($keys);
                } else {
                    $platform = $result[self::PLATFORM][0];
                }
            } elseif (isset($result[self::PLATFORM][0])) {
                $platform = $result[self::PLATFORM][0];
            }
        }

        if ($platform == 'linux-gnu' || $platform == 'X11') {
            $platform = 'Linux';
        } elseif ($platform == 'CrOS') {
            $platform = 'Chrome OS';
        }

        preg_match_all(<<<'REGEX'
%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
TizenBrowser|(?:Headless)?Chrome|YaBrowser|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|Edg|CriOS|UCBrowser|Puffin|OculusBrowser|SamsungBrowser|
Baiduspider|Applebot|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
Valve\ Steam\ Tenfoot|
NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
(?:\)?;?)
(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix
REGEX
            , $u_agent, $result);

        // If nothing matched, return null (to avoid undefined index errors)
        if (!isset($result[self::BROWSER][0]) || !isset($result[self::BROWSER_VERSION][0])) {
            if (preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result)) {
                return array(
                    self::PLATFORM => $platform ?: null,
                    self::BROWSER => $result[self::BROWSER],
                    self::BROWSER_VERSION => empty($result[self::BROWSER_VERSION]) ? null : $result[self::BROWSER_VERSION]
                );
            }

            return $empty;
        }

        if (preg_match('/rv:(?P<version>[0-9A-Z.]+)/i', $u_agent, $rv_result)) {
            $rv_result = $rv_result[self::BROWSER_VERSION];
        }

        $browser = $result[self::BROWSER][0];
        $version = $result[self::BROWSER_VERSION][0];

        $lowerBrowser = array_map('strtolower', $result[self::BROWSER]);

        $find = function ($search, &$key = null, &$value = null) use ($lowerBrowser) {
            $search = (array)$search;

            foreach ($search as $val) {
                $xkey = array_search(strtolower($val), $lowerBrowser);
                if ($xkey !== false) {
                    $value = $val;
                    $key = $xkey;

                    return true;
                }
            }

            return false;
        };

        $findT = function (array $search, &$key = null, &$value = null) use ($find) {
            $value2 = null;
            if ($find(array_keys($search), $key, $value2)) {
                $value = $search[$value2];

                return true;
            }

            return false;
        };

        $key = 0;
        $val = '';
        if ($findT(array(
            'OPR' => 'Opera',
            'UCBrowser' => 'UC Browser',
            'YaBrowser' => 'Yandex',
            'Iceweasel' => 'Firefox',
            'Icecat' => 'Firefox',
            'CriOS' => 'Chrome',
            'Edg' => 'Edge'
        ), $key, $browser)) {
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($find('Playstation Vita', $key, $platform)) {
            $platform = 'PlayStation Vita';
            $browser = 'Browser';
        } elseif ($find(array('Kindle Fire', 'Silk'), $key, $val)) {
            $browser = $val == 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if (!($version = $result[self::BROWSER_VERSION][$key]) || !is_numeric($version[0])) {
                $version = $result[self::BROWSER_VERSION][array_search('Version', $result[self::BROWSER])];
            }
        } elseif ($find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS') {
            $browser = 'NintendoBrowser';
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($find('Kindle', $key, $platform)) {
            $browser = $result[self::BROWSER][$key];
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($find('Opera', $key, $browser)) {
            $find('Version', $key);
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($find('Puffin', $key, $browser)) {
            $version = $result[self::BROWSER_VERSION][$key];
            if (strlen($version) > 3) {
                $part = substr($version, -2);
                if (ctype_upper($part)) {
                    $version = substr($version, 0, -2);

                    $flags = array(
                        'IP' => 'iPhone',
                        'IT' => 'iPad',
                        'AP' => 'Android',
                        'AT' => 'Android',
                        'WP' => 'Windows Phone',
                        'WT' => 'Windows'
                    );
                    if (isset($flags[$part])) {
                        $platform = $flags[$part];
                    }
                }
            }
        } elseif ($find(array(
            'Applebot',
            'IEMobile',
            'Edge',
            'Midori',
            'Vivaldi',
            'OculusBrowser',
            'SamsungBrowser',
            'Valve Steam Tenfoot',
            'Chrome',
            'HeadlessChrome'
        ), $key, $browser)) {
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($rv_result && $find('Trident')) {
            $browser = 'MSIE';
            $version = $rv_result;
        } elseif ($browser == 'AppleWebKit') {
            if ($platform == 'Android') {
                $browser = 'Android Browser';
            } elseif (strpos($platform, 'BB') === 0) {
                $browser = 'BlackBerry Browser';
                $platform = 'BlackBerry';
            } elseif ($platform == 'BlackBerry' || $platform == 'PlayBook') {
                $browser = 'BlackBerry Browser';
            } else {
                $find('Safari', $key, $browser) || $find('TizenBrowser', $key, $browser);
            }

            $find('Version', $key);
            $version = $result[self::BROWSER_VERSION][$key];
        } elseif ($pKey = preg_grep('/playstation \d/i', $result[self::BROWSER])) {
            $pKey = reset($pKey);

            $platform = 'PlayStation ' . preg_replace('/\D/', '', $pKey);
            $browser = 'NetFront';
        }

        return array(
            self::PLATFORM => $platform ?: null,
            self::BROWSER => $browser ?: null,
            self::BROWSER_VERSION => $version ?: null
        );
    }
}