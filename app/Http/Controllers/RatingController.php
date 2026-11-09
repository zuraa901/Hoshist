<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RatingController extends Controller
{

    public function create(Request $request): View
    {
        $selectedTitle = null;
        $selectedId = (int) $request->query('judul', 0);

        if ($selectedId > 0) {
            $selectedTitle = DB::table('titles')
                ->where('id', $selectedId)
                ->select('id', 'title', 'type')
                ->first();
        }

        return view('rating-create', compact('selectedTitle'));
    }

  
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            [
                'title_id' => ['required', 'integer', 'exists:titles,id'],
                'score'    => ['required', 'integer', 'between:1,10'],
                'review'   => ['nullable', 'string', 'max:1000'],
            ],
            [
                'title_id.required' => 'Pilih judul dari daftar yang muncul saat kamu mengetik.',
                'title_id.integer'  => 'Pilih judul dari daftar yang muncul saat kamu mengetik.',
                'title_id.exists'   => 'Judul yang dipilih tidak ditemukan.',
                'score.required'    => 'Pilih skor 1 sampai 10.',
                'score.between'     => 'Skor harus antara 1 sampai 10.',
                'review.max'        => 'Review maksimal 1000 karakter.',
            ]
        );

        $review = isset($data['review']) ? trim($data['review']) : '';
        $review = $review !== '' ? $review : null;

        $now    = now();
        $where  = ['user_id' => Auth::id(), 'title_id' => $data['title_id']];
        $values = ['score' => $data['score'], 'review' => $review, 'updated_at' => $now];

        if (DB::table('ratings')->where($where)->exists()) {
            DB::table('ratings')->where($where)->update($values);
        } else {
            DB::table('ratings')->insert($where + $values + ['created_at' => $now]);
        }

        return redirect()
            ->route('detail.show', $data['title_id'])
            ->with('status', 'Rating tersimpan.');
    }
}