<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'breaks' => 'array',
            'breaks.*.start' => 'nullable|date_format:H:i|after:clock_in|before:clock_out',
            'breaks.*.end' => 'nullable|date_format:H:i|after:breaks.*.start|before:clock_out',
            'memo' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.date_format' => '出勤時間はHH:MM形式で入力してください',
            'clock_out.date_format' => '退勤時間はHH:MM形式で入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.start.date_format' => '休憩開始時間はHH:MM形式で入力してください',
            'breaks.*.start.after' => '休憩時間が不適切な値です',
            'breaks.*.start.before' => '休憩時間が不適切な値です',
            'breaks.*.end.date_format' => '休憩終了時間はHH:MM形式で入力してください',
            'breaks.*.end.after' => '休憩時間が不適切な値です',
            'breaks.*.end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'memo.required' => '備考を記入してください',
        ];
    }
}
