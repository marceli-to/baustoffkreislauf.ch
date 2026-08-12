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
      'has_button_additional_individuals' => $course->has_button_additional_individuals,
      'has_field_additional_individual_salutation' => $course->has_field_additional_individual_salutation,
      'has_field_additional_individual_email' => $course->has_field_additional_individual_email,
      'has_field_additional_individual_name' => $course->has_field_additional_individual_name,
      'has_field_additional_individual_firstname' => $course->has_field_additional_individual_firstname,
      'has_field_additional_individual_cost_center' => $course->has_field_additional_individual_cost_center,
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
    $date = \Carbon\Carbon::parse($course->course_date)->locale($request->input('locale'));
    
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
      // 'location' => $request->input('location'),
      'zip' => $request->input('zip'),
      'city' => $request->input('city'),
      'address' => $request->input('address'),
      'remarks' => $request->input('remarks'),
      'cost_center' => $request->input('cost_center'),
      'locale' => $request->input('locale'),
    ];

    // handle additional individuals, build a string out of:
    // salutation, email, firstname, name and cost center (if available)
    $additional_individuals = [];
    foreach ($request->input('additional_individuals') ?? [] as $additional_individual)
    {
      $additional_individual_data = [
        'salutation' => $additional_individual['salutation'] ?? null,
        'email' => $additional_individual['email'] ?? null,
        'name' => trim(($additional_individual['firstname'] ?? '') . ' ' . ($additional_individual['name'] ?? '')),
        'cost_center' => $additional_individual['cost_center'] ?? null,
      ];

      // create comma separated string
      $additional_individuals[] = implode(', ', array_filter($additional_individual_data));
    }

    // add newline instead of comma
    $data['additional_individuals'] = implode("\n", $additional_individuals);

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
        if (strpos($field, 'additional_individuals.') === 0) {
          $parts = explode('.', $field);
          $index = $parts[1];
          $subfield = $parts[2];
          $formattedErrors['additional_individuals'][$index][$subfield] = $messages[0];
        }
        else {
          $formattedErrors[$field] = $messages[0];
        }
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
      $validationRules['zip'] = 'required';
      $validationRules['city'] = 'required';
    }

    if ($course->has_address && $course->requires_address) {
      $validationRules['address'] = 'required';
    }

    if ($course->has_cost_center && $course->requires_cost_center) {
      $validationRules['cost_center'] = 'required';
    }

    if ($course->has_button_additional_individuals) {
      if ($course->has_field_additional_individual_name) {
        $validationRules['additional_individuals.*.name'] = 'required';
      }

      if ($course->has_field_additional_individual_firstname) {
        $validationRules['additional_individuals.*.firstname'] = 'required';
      }

      if ($course->has_field_additional_individual_email) {
        $validationRules['additional_individuals.*.email'] = 'required|email|regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
      }

      if ($course->has_field_additional_individual_cost_center) {
        $validationRules['additional_individuals.*.cost_center'] = 'required';
      }
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
      // 'location.required' => __('Ort ist erforderlich'),
      'zip.required' => __('PLZ ist erforderlich'),
      'city.required' => __('Ort ist erforderlich'),
      'address.required' => __('Adresse ist erforderlich'),
      'cost_center.required' => __('Kostenstelle ist erforderlich'),
      'additional_individuals.*.name.required' => __('Name ist erforderlich'),
      'additional_individuals.*.firstname.required' => __('Vorname ist erforderlich'),
      'additional_individuals.*.email.required' => __('E-Mail-Adresse ist erforderlich'),
      'additional_individuals.*.email.email' => __('E-Mail-Adresse muss gültig sein'),
      'additional_individuals.*.email.regex' => __('E-Mail-Adresse muss gültig sein'),
      'additional_individuals.*.cost_center.required' => __('Kostenstelle ist erforderlich'),
      'toc.accepted' => __('Sie müssen die Teilnahme- und Annullationsbedingungen sowie die Datenschutzbestimmungen akzeptieren'),
    ];
    
    return [
      'rules' => $validationRules,
      'messages' => $validationMessages,
    ];
  }
}