<?php

namespace Elbucho\AlpacaV2\API;

class Helper
{
    /**
     * Translate a date string into a \DateTimeImmutable object
     *
     * @static
     * @access  public
     * @param   string  $datetime
     * @return  \DateTimeImmutable
     * @throws  \Exception
     */
    static public function convertToDateTime(string $datetime): \DateTimeImmutable
    {
        $pattern = "/^(?<year>\d{4})\-(?<month>\d{2})\-(?<day>\d{2})T(?<hour>\d{2})\:" .
            "(?<minute>\d{2})\:(?<second>\d{2})(\.\d*)?(?<offset>\-\d{2}\:\d{2})?/";
        preg_match($pattern, $datetime, $match);

        foreach (['year','month','day','hour','minute','second'] as $required) {
            if ( ! array_key_exists($required, $match)) {
                throw new \Exception(sprintf(
                    'Provided timestamp does not conform to required format: %s',
                    $datetime
                ));
            }
        }

        $offset = (isset($match['offset']) ? $match['offset'] : '-00:00');

        $formattedTime = sprintf(
            '%s-%s-%sT%s:%s:%s%s',
            $match['year'],
            $match['month'],
            $match['day'],
            $match['hour'],
            $match['minute'],
            $match['second'],
            $offset
        );

        return new \DateTimeImmutable($formattedTime);
    }
}