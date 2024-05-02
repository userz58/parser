<?php

namespace App\Formatter;

/**
 *  in:
 *  https://www.atlascopco.com/content/dam/pim/itba/atlas-copco/marketing/Sockets and Bits.tif/jcr:content/renditions/cq5dam.web.800.800.jpeg
 *
 *  out:
 *  https://www.atlascopco.com/content/dam/pim/itba/atlas-copco/marketing/Sockets%20and%20Bits.tif/jcr:content/renditions/cq5dam.web.800.800.jpeg
 */
class AtlasCopcoUrlFormatter implements FormatterInterface
{
    public function format(string|null $str): ?string
    {
        if (null === $str || $str === '') {
            return null;
        }

        if (preg_match('#^([\w\d]+://)([^/]+)(.*)$#iu', $str, $m)) {
            $str = $m[1] . idn_to_ascii($m[2], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) . $m[3];
        }

        $str = urldecode($str);
        $str = rawurlencode($str);
        $str = str_replace(['%3A', '%2F',], [':', '/',], $str);

        return $str;
    }
}
