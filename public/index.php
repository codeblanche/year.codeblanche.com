<?php

ini_set('xdebug.var_display_max_depth', 10);

class Calendar
{
    const DAY_INTERVAL = 86400;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @var array
     */
    protected $weekBase = array(
        'MON' => null,
        'TUE' => null,
        'WED' => null,
        'THU' => null,
        'FRI' => null,
        'SAT' => null,
        'SUN' => null,
    );

    /**
     * @var int
     */
    protected $year;

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->year = date('Y');
        $matches    = array();

        if (preg_match('/^[0-9]{4}/', filter_input(INPUT_SERVER, 'HTTP_HOST'), $matches)) {
            $this->year = $matches[0];
        }

        $this->timestamp = strtotime("$this->year-01-01");
        $dowMap          = array('SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT');

        while (date('Y', $this->timestamp) === $this->year) {
            $monthNumber = (int) date('n', $this->timestamp);
            $weekOfYear  = (int) date('W', $this->timestamp);
            $dayOfWeek   = $dowMap[date('w', $this->timestamp)];

            if (empty($this->data['year'][$this->year]['month'][$monthNumber])) {
                $this->data['year'][$this->year]['month'][$monthNumber]['name'] = date('F', $this->timestamp);
            }

            if (empty($this->data['year'][$this->year]['month'][$monthNumber]['week'][$weekOfYear])) {
                $this->data['year'][$this->year]['month'][$monthNumber]['week'][$weekOfYear]['day'] = $this->weekBase;
            }

            $this->data['year'][$this->year]['month'][$monthNumber]['week'][$weekOfYear]['day'][$dayOfWeek] = array(
                'day'   => date('j', $this->timestamp),
                'date'  => date('Y-m-d', $this->timestamp),
                'today' => date('Y-m-d', $this->timestamp) === date('Y-m-d'),
            );

            $this->timestamp += self::DAY_INTERVAL;
        }
    }

    /**
     * Magic method
     *
     * @return string;
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Dump the data array
     */
    public function dump()
    {
        var_dump($this->data);
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Output calendar
     *
     * @return string
     */
    public function toString()
    {
        $result = '';

        foreach ($this->data['year'] as $year => $yearData) {
            $result .= $this->yearToString($year, $yearData);
        }

        return $result;
    }

    /**
     * @param string $day
     * @param array  $data
     *
     * @return string
     */
    protected function dayToString($day, array $data = null)
    {
        $result    = '';
        $dayNumber = '&nbsp;';
        $today     = '';
        $weekend   = '';

        if (!empty($data)) {
            $dayNumber = $data['day'];

            if ($data['today']) {
                $today = 'today';
            }
        }

        if (in_array($day, array('SAT', 'SUN'))) {
            $weekend = 'weekend';
        }

        $result .= '<div class="day ' . $today . ' ' . $weekend . '">' . $dayNumber . '</div>';

        return $result;
    }

    /**
     * @param int   $month
     * @param array $data
     *
     * @return string
     */
    protected function monthToString($month, array $data)
    {
        $result = '';

        $result .= '<div class="month">';
        $result .= '<div class="month-title">' . $data['name'] . '</div>';
        $result .= '<div class="week day-title-row"><div class="day day-title week-number"> &nbsp; </div>';

        foreach ($this->weekBase as $dow => $null) {
            $weekend = '';

            if (in_array($dow, array('SAT', 'SUN'))) {
                $weekend = 'weekend';
            }

            $result .= '<div class="day day-title ' . $weekend . '">' . substr($dow, 0, 1) . '</div>';
        }

        $result .= '<div class="cl"></div>';
        $result .= '</div>';

        foreach ($data['week'] as $week => $weekData) {
            $result .= $this->weekToString($week, $weekData);
        }

        $result .= '<div class="cl"></div>';
        $result .= '</div>';

        return $result;
    }

    /**
     * @param int   $week
     * @param array $data
     *
     * @return string
     */
    protected function weekToString($week, array $data)
    {
        $result = '';

        $result .= '<div class="week">';
        $result .= '<div class="day week-number">' . $week . '</div>';

        foreach ($data['day'] as $day => $dayData) {
            $result .= $this->dayToString($day, $dayData);
        }

        $result .= '<div class="cl"></div>';
        $result .= '</div>';

        return $result;
    }

    /**
     * @param string $year
     * @param array  $data
     *
     * @return string
     */
    protected function yearToString($year, array $data)
    {
        $result = '';

        $result .= '<div class="year">' . $year . '</div>';

        foreach ($data['month'] as $month => $monthData) {
            $result .= $this->monthToString($month, $monthData);
        }

        $result .= '<div class="cl"></div>';

        return $result;
    }
}

$cal = new Calendar();

?>

<!doctype html>
<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="icon" href="http://codeblanche.com/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="http://codeblanche.com/favicon.ico" type="image/x-icon" />
    <title><?= $cal->getYear(); ?>></title>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'/>
    <link rel="stylesheet" type="text/css" href="/css/main.css"/>
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-38104019-1']);
        _gaq.push(['_trackPageview']);
        
        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    </script>
</head>
<body class="dark">
<div class="main">
<?= $cal; ?>
<div class="copyright">Powered by <a href="http://codeblanche.com" target="_blank">CodeBlanche</a> &copy; 2013</div>
</div>
</body>
</html>


