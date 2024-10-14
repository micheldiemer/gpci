<?php

class Profiler
{
    private array $p_times;

    public function __construct()
    {
        $this->start();
    }

    public function start()
    {
        $this->p_times = [];
        $this->open('__start');
    }

    public function open(string $flag): void
    {
        if (! array_key_exists($flag, $this->p_times))
            $this->p_times[$flag] = ['total' => 0, 'open' => 0];
        $$this->p_times[$flag]['open'] = microtime(true);
    }

    public function close(string $flag): void
    {
        if (isset($this->p_times[$flag]['open'])) {
            $this->p_times[$flag]['total'] += (microtime(true) - $this->p_times[$flag]['open']);
            unset($p_times[$flag]['open']);
        } else {
            $this->p_times[$flag]['total']
                += (microtime(true) - $this->p_times['__start']['open']);
        }
    }

    public function __toString(): string
    {
        return json_encode($this->p_times);
    }

    public function end(): string
    {
        $dump = [];
        $str = '';
        // $sum  = 0;
        $total = microtime(true) - $this->p_times['__start']['open'];
        $str .= "[\n";
        foreach ($this->p_times as $flag => $info) {
            $dump[$flag]['elapsed'] = $info['total'];
            $dump[$flag]['percent'] = $info['total'] / $total;
            // $sum += $info['total'];
        }
        $str .= "\n]\n";
        $this->start();
        return json_encode($dump);
    }
}
