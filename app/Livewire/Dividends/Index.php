<?php

namespace App\Livewire\Dividends;

use App\Models\Contribution;
use App\Models\Member;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public int $year;
    public $surplus = 0;
    public $basis = 'shares'; // shares | total (shares+welfare+merry)

    public $results = [];
    public $calculated = false;

    public function mount(): void
    {
        $this->year = (int) now()->year;
    }

    protected function rules(): array
    {
        return [
            'year'    => 'required|integer|min:2000|max:2100',
            'surplus' => 'required|numeric|min:0',
            'basis'   => 'required|in:shares,total',
        ];
    }

    public function calculate(): void
    {
        $this->validate();

        $start = Carbon::create($this->year, 1, 1)->startOfYear();
        $end   = $start->copy()->endOfYear();

        // Sum per member within the year based on selected basis.
        $query = Contribution::whereBetween('paid_at', [$start, $end]);

        if ($this->basis === 'shares') {
            $query->selectRaw('member_id, SUM(shares) as score');
        } else {
            $query->selectRaw('member_id, SUM(shares + welfare + merry_go_round) as score');
        }

        $perMember = $query->groupBy('member_id')->pluck('score', 'member_id');
        $totalScore = (float) $perMember->sum();

        if ($totalScore <= 0) {
            $this->results = [];
            $this->calculated = true;
            session()->flash('error', 'No contributions found for that year — nothing to share.');
            return;
        }

        $members = Member::whereIn('id', $perMember->keys())->get()->keyBy('id');
        $surplus = (float) $this->surplus;

        $rows = [];
        foreach ($perMember as $memberId => $score) {
            $pct    = $score / $totalScore;
            $share  = round($surplus * $pct, 2);
            $member = $members[$memberId] ?? null;
            $rows[] = [
                'member_id' => $memberId,
                'name'      => $member?->full_name ?? 'Member #' . $memberId,
                'score'     => (float) $score,
                'percent'   => round($pct * 100, 2),
                'dividend'  => $share,
            ];
        }

        usort($rows, fn ($a, $b) => $b['dividend'] <=> $a['dividend']);

        $this->results = $rows;
        $this->calculated = true;
    }

    public function exportCsv()
    {
        if (! $this->calculated) {
            $this->calculate();
        }

        $rows     = $this->results;
        $year     = $this->year;
        $basis    = $this->basis;

        return response()->streamDownload(function () use ($rows, $basis) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Member', 'Contribution basis (' . $basis . ')', 'Percent (%)', 'Dividend (KES)']);
            foreach ($rows as $r) {
                fputcsv($out, [$r['name'], $r['score'], $r['percent'], $r['dividend']]);
            }
            fclose($out);
        }, "dividends-{$year}.csv", ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        return view('livewire.dividends.index');
    }
}
