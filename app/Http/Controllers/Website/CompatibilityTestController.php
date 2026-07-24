<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\EducationalLevel;
use App\Models\MarriageType;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionCategory;
use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompatibilityTestController extends Controller
{
    public function test()
    {
        $categories = QuestionCategory::orderBy('order','asc')->with('questions', 'questions.answers')->where('type', 'test')->get();
        return view('website.compatibility_test.compatibility_test', compact('categories'));
    }

    public function completeTest(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = auth('web')->user();
            $questions = $request->input('questions', []);
            $answers = $request->input('answers', []);

            foreach ($questions as $index => $questionId) {
                $answerValue = $answers[$index] ?? null;
                if ($answerValue !== null) {
                    $data = [
                        'user_id' => $user->id,
                        'question_id' => $questionId,
                        'exam_type' => 'test',
                    ];
                    if (is_numeric($answerValue)) {
                        $data['question_answer_id'] = (int)$answerValue;
                        $answer = QuestionAnswer::find($answerValue);
                        $data['answer_text'] = $answer->answer;
                    } else {
                        $data['question_answer_id'] = null;
                        $data['answer_text'] = $answerValue;
                    }
                    $question = Question::find($questionId);
                    $category = QuestionCategory::find($question->question_category_id);
                    $data['question'] = $question->getTranslations('question');
                    UserAnswer::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'question_id' => $questionId,
                            'exam_type' => 'test',
                            'category' => $category->name,
                        ],
                        $data
                    );
                }
            }
            DB::commit();
            return redirect()->route('website.compatibility_test.test_result')->with('success', 'تم اتمام الاختبار بنجاح!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}
