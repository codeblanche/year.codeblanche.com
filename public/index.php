<?php

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
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="format-detection" content="telephone=no">
    <link rel="icon" href="http://codeblanche.com/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="http://codeblanche.com/favicon.ico" type="image/x-icon" />
    <title><?= $cal->getYear(); ?></title>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'/>
    <link rel="stylesheet" type="text/css" href="/css/main.css"/>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-42443644-1', 'codeblanche.com');
        ga('send', 'pageview');

    </script>
</head>
<body class="dark">
<div class="main">
<?= $cal; ?>
<div class="copyright">Powered by <a href="http://codeblanche.com" target="_blank">CodeBlanche</a> &copy; 2013</div>
</div>
</body>
<script type=text/javascript>
    setTimeout(function () {
        var today = document.getElementsByClassName('today')[0];
        var main  = document.getElementsByClassName('main')[0];
        var speed = 0.3;

        if (typeof main === 'undefined' || typeof today === 'undefined') {
            return;
        }

        var todayOffsetTop = today.offsetTop;
        var targetScrollY  = todayOffsetTop - document.body.offsetHeight / 2;

        function scrollToToday() {
            var move = Math.max((targetScrollY - window.scrollY) * speed, 1);

            window.scrollBy(0, move);

            if (move === 1) {
                return;
            }

            setTimeout(scrollToToday, 20);
        }

        if (window.scrollY === 0 && main.offsetHeight > document.body.offsetHeight) {
            scrollToToday();
        }
    }, 1000);
</script>
</html>


