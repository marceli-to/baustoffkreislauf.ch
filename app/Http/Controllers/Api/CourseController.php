<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserCourseRegistration;
use App\Notifications\OwnerCourseRegistration;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
  public function get($courseId, $locale = 'de')
  {
    $course = Entry::find($courseId, $locale);
    return response()->json([
      'title' => $course->title,
      'has_salutation' => $course->has_salutation,
      'requires_salutation' => $course->requires_salutation,
      'has_name' => $course->has_name,
      'requires_name' => $course->requires_name,
      'has_firstname' => $course->has_firstname,
      'requires_firstname' => $course->requires_firstname,
      'has_email' => $course->has_email,
      'requires_email' => $course->requires_email,
      'has_phone' => $course->has_phone,
      'requires_phone' => $course->requires_phone,
      'has_company' => $course->has_company,
      'requires_company' => $course->requires_company,
      'has_location' => $course->has_location,
      'requires_location' => $course->requires_location,
      'has_address' => $course->has_address,
      'requires_address' => $course->requires_address,
      'has_cost_center' => $course->has_cost_center,
      'requires_cost_center' => $course->requires_cost_center,
      'has_remarks' => $course->has_remarks,
    ]);
  }

  public function register(Request $request)
  {
    $course = Entry::find($request->input('course_id'));

    $validationResult = $this->validateRequest($request, $course);
    if ($validationResult !== TRUE) {
      return $validationResult;
    }

    $slug = $course->title . ' ' . $request->input('firstname') . ' ' . $request->input('name');

    // build data
    $date = Carbon::parse($course->course_date)->locale($request->input('locale'));
    $data = [
      'title' => $course->title,
      'date' => $date->translatedFormat('d. F Y'),
      'course_id' => $course->id,
      'salutation' => $request->input('salutation'),
      'name' => $request->input('name'),
      'firstname' => $request->input('firstname'),
      'email' => $request->input('email'),
      'phone' => $request->input('phone'),
      'company' => $request->input('company'),
      'location' => $request->input('location'),
      'address' => $request->input('address'),
      'remarks' => $request->input('remarks'),
      'cost_center' => $request->input('cost_center'),
      'locale' => $request->input('locale'),
    ];

    $entry = Entry::make()
      ->collection('course_registrations')
      ->slug($slug)
      ->data($data)
      ->save();
    
    Notification::route('mail', $request->input('email'))
      ->notify(new UserCourseRegistration($data)
    );

    Notification::route('mail', env('MAIL_TO'))
      ->notify(new OwnerCourseRegistration($data)
    );

    return response()->json(['message' => 'Store successful']);
  }

  protected function validateRequest(Request $request, $course)
  {
    $validationRules = $this->getValidationRules($course);

    $validator = Validator::make(
      $request->all(),
      $validationRules['rules'],
      $validationRules['messages']
    );

    if ($validator->fails())
    {
      $errors = $validator->errors();
      $formattedErrors = [];

      foreach ($errors->messages() as $field => $messages)
      {
        $formattedErrors[$field] = $messages[0];
      }

      return response()->json(['errors' => $formattedErrors], 422);
    }

    return TRUE;
  }

  protected function getValidationRules($course)
  {
    $validationRules = [];

    if ($course->has_salutation && $course->requires_salutation) {
      $validationRules['salutation'] = 'required';
    }

    if ($course->has_name && $course->requires_name) {
      $validationRules['name'] = 'required';
    }

    if ($course->has_firstname && $course->requires_firstname) {
      $validationRules['firstname'] = 'required';
    }

    if ($course->has_email && $course->requires_email) {
      $validationRules['email'] = 'required|email|regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    } 

    if ($course->has_phone && $course->requires_phone) {
      $validationRules['phone'] = 'required';
    }

    if ($course->has_company && $course->requires_company) {
      $validationRules['company'] = 'required';
    }

    if ($course->has_location && $course->requires_location) {
      $validationRules['location'] = 'required';
    }

    if ($course->has_address && $course->requires_address) {
      $validationRules['address'] = 'required';
    }

    if ($course->has_cost_center && $course->requires_cost_center) {
      $validationRules['cost_center'] = 'required';
    }

    $validationRules['toc'] = 'accepted';

    // Set validation messages
    $validationMessages = [
      'salutation.required' => __('Anrede ist erforderlich'),
      'name.required' => __('Name ist erforderlich'),
      'firstname.required' => __('Vorname ist erforderlich'),
      'email.required' => __('E-Mail-Adresse ist erforderlich'),
      'email.email' => __('E-Mail-Adresse muss gültig sein'),
      'email.regex' => __('E-Mail-Adresse muss gültig sein'),
      'phone.required' => __('Telefonnummer ist erforderlich'),
      'company.required' => __('Firma ist erforderlich'),
      'location.required' => __('Ort ist erforderlich'),
      'address.required' => __('Adresse ist erforderlich'),
      'cost_center.required' => __('Kostenstelle ist erforderlich'),
      'toc.accepted' => __('Sie müssen die Teilnahme- und Annullationsbedingungen sowie die Datenschutzbestimmungen akzeptieren'),
    ];
    
    return [
      'rules' => $validationRules,
      'messages' => $validationMessages,
    ];
  }
}