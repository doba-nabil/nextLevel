<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Country;
use App\Models\EducationalLevel;
use App\Models\Favourite;
use App\Models\MarriageType;
use App\Models\Profile;
use App\Models\ProfileVisit;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionCategory;
use App\Models\Service;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserSubscription;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MyAccountController extends Controller
{
    public function my_account()
    {
        $user = auth('web')->user();
        $countries = Country::all();
        $subscribe = Subscribe::where('email', auth('web')->user()->email)->first();
        $chats = Chat::with(['fromUser', 'toUser', 'lastMessage', 'unreadMessages'])
            ->where(function ($q) use ($user) {
                $q->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            })
            ->latest('updated_at')
            ->get();
        $favourites = Favourite::where('user_id', $user->id)->orderBy('id','desc')->get();
        $subscriptions = UserSubscription::where('user_id', auth('web')->id())->orderBy('id', 'desc')->get();
        $package = Service::orderByDesc('price')->where('type','package')->first();
        $has_test = UserSubscription::where('type', 'question')->where('user_id', auth('web')->id())->first();
        $done_test = UserAnswer::where('exam_type', 'test')->where('user_id', auth('web')->id())->first();
        return view('website.my_account.profile', compact('user', 'subscribe', 'countries', 'chats', 'favourites', 'subscriptions', 'package', 'has_test', 'done_test'));
    }

    public function update_account(Request $request)
    {
        $country = Country::find($request->country_id);
        DB::beginTransaction();
        User::where('id', auth('web')->user()->id)->update([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        Profile::where('user_id', auth('web')->user()->id)->update([
            'country_id' => $request->country_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'country' => $country?->getTranslations('name'),
        ]);
        DB::commit();
        return redirect()->back()->withSuccess(__('website.personal_info_updated_successfully'));
    }

    public function update_password(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => __('website.current_password_required'),
            'new_password.required' => __('website.new_password_required'),
            'new_password.min' => __('website.password_min_length'),
            'new_password.confirmed' => __('website.password_confirmation_mismatch'),
        ]);

        $user = auth('web')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', __('website.current_password_incorrect'));
        }
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        return back()->with('success', __('website.password_changed_successfully'));
    }

    public function subscribe_me()
    {
        $subscribe = Subscribe::where('email', auth('web')->user()->email)->first();
        if ($subscribe) {
            $subscribe->delete();
            $message = __('website.unsubscribed_from_mailing_list');
        }
        if (!$subscribe) {
            Subscribe::create([
                'email' => auth('web')->user()->email,
            ]);
            $message = __('website.subscribed_to_mailing_list');
        }
        return redirect()->back()->withSuccess($message);
    }

    public function completeProfile()
    {
        $countries = Country::all();
        $marriage_types = MarriageType::all();
        $educational_levels = EducationalLevel::all();
        $question_categories = QuestionCategory::whereHas('questions')->orderBy('order', 'asc')->where([['is_active', 1], ['type', 'profile']])->get();
        return view('website.my_account.complete_profile', compact('countries', 'marriage_types', 'educational_levels', 'question_categories'));
    }

    public function completeProfilePost(Request $request)
    {
        $request->validate([
            'marriage_type_id' => ['required', 'exists:marriage_types,id'],
            'marital_status' => ['required', 'string'],
            'birth_date' => ['required', 'date'],
            'religion' => ['required', 'in:muslim,christian,other'],
            'gender' => ['required', 'in:male,female'],
            'nationality_id' => ['required', 'exists:countries,id'],
            'job' => ['required', 'string', 'max:255'],
            'educational_level_id' => ['required', 'exists:educational_levels,id'],
            'polygamy' => ['required', 'in:yes,no'],
            'weight' => ['required', 'numeric', 'min:30', 'max:300'],
            'height' => ['required', 'numeric', 'min:0.5', 'max:250'],
            'my_desires' => ['nullable', 'string'],
            'about_me' => ['nullable', 'string'],
            'hijab' => ['nullable', 'in:yes,no'],
            'terms' => ['accepted'],
        ], [
            'marriage_type_id.required' => __('website.marriage_type_required'),
            'marriage_type_id.exists' => __('website.marriage_type_not_found'),

            // marital status
            'marital_status.required' => __('website.marital_status_required'),

            // age
            'birth_date.required' => __('website.birth_date_required'),
            'birth_date.date' => __('website.birth_date_invalid'),

            // religion
            'religion.required' => __('website.religion_required'),
            'religion.in' => __('website.religion_invalid'),

            // gender
            'gender.required' => __('website.gender_required'),
            'gender.in' => __('website.gender_invalid'),

            // nationality
            'nationality_id.required' => __('website.nationality_required'),
            'nationality_id.exists' => __('website.nationality_not_found'),

            // job
            'job.required' => __('website.job_required'),
            'job.max' => __('website.job_max_length'),

            // education
            'educational_level_id.required' => __('website.education_level_required'),
            'educational_level_id.exists' => __('website.education_level_not_found'),

            // polygamy
            'polygamy.required' => __('website.polygamy_required'),
            'polygamy.in' => __('website.polygamy_invalid'),

            // weight
            'weight.required' => __('website.weight_required'),
            'weight.numeric' => __('website.weight_must_be_numeric'),
            'weight.min' => __('website.weight_min_value'),
            'weight.max' => __('website.weight_max_value'),

            // height
            'height.required' => __('website.height_required'),
            'height.numeric' => __('website.height_must_be_numeric'),
            'height.min' => __('website.height_min_value'),
            'height.max' => __('website.height_max_value'),

            // hijab
            'hijab.in' => __('website.hijab_invalid'),

            // terms
            'terms.accepted' => __('website.terms_must_be_accepted'),

            // my_desires & about_me
            'my_desires.string' => __('website.desires_must_be_string'),
            'about_me.string' => __('website.about_me_must_be_string'),
        ]);

        $nationality = Country::find($request->nationality_id);
        $marriage_type = MarriageType::find($request->marriage_type_id);
        $educational_level = EducationalLevel::find($request->educational_level_id);
        DB::beginTransaction();

        try {
            $user = auth('web')->user();

            $user->profile->update([
                'birth_date' => $request->birth_date,
                'marriage_type_id' => $request->marriage_type_id,
                'marriage_type' => $marriage_type->name,
                'martial_status' => $request->martial_status,
                'educational_level_id' => $request->educational_level_id,
                'educational_level' => $educational_level->name,
                'birth_date' => $request->birth_date,
                'religion' => $request->religion,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'nationality_id' => $request->nationality_id,
                'nationality' => $nationality->name,
                'hijab' => $request->gender == 'male' ? '' : $request->hijab,
                'job' => $request->job,
                'education_id' => $request->martial_status,
                'polygamy' => $request->polygamy,
                'weight' => $request->weight,
                'height' => $request->height,
                'my_desires' => $request->my_desires,
                'about_me' => $request->about_me,
            ]);

            $questions = $request->input('questions', []);
            $answers = $request->input('answers', []);

            foreach ($questions as $index => $questionId) {
                $answerValue = $answers[$index] ?? null;
                if ($answerValue !== null) {
                    $data = [
                        'user_id' => $user->id,
                        'question_id' => $questionId,
                        'exam_type' => 'profile',
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
                            'exam_type' => 'profile',
                            'category' => $category->name,
                        ],
                        $data
                    );
                }
            }
            DB::commit();
            return redirect()->route('website.home')->with('success', __('website.data_saved_successfully'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function fav($user_uuid)
    {
        $user = auth()->user();
        $otherUser = User::where('uuid', $user_uuid)->firstOrFail();
        $favorite = Favourite::where('user_id', $user->id)
            ->where('favourite_user_id', $otherUser->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $message = __('website.user_removed_from_favorites');
        } else {
            Favourite::create([
                'user_id' => $user->id,
                'favourite_user_id' => $otherUser->id,
            ]);
            $message = __('website.user_added_to_favorites');
        }
        return redirect()->back()->withSuccess($message);
    }

    public function my_favotites()
    {
        $user = auth('web')->user();
        $favourites = Favourite::where('user_id', $user->id)->orderBy('id','desc')->paginate(15);
        return view('website.my_account.favorites', compact('user','favourites'));
    }

    public function myVisitors()
    {
        $visits = ProfileVisit::where('visited_id', auth()->id())
            ->with('visitor')
            ->orderBy('visited_at', 'desc')
            ->paginate(10);

        return view('website.my_account.visitors', compact('visits'));
    }

}
