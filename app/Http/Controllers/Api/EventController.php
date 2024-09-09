<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserEventRegistrationNotification;
use App\Notifications\OwnerEventRegistrationNotification;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
  public function get($eventId)
  {
    $event = Entry::find($eventId, 'de');
    return response()->json([
      'title' => $event->title,
      'has_salutation' => $event->has_salutation,
      'requires_salutation' => $event->requires_salutation,
      'has_name' => $event->has_name,
      'requires_name' => $event->requires_name,
      'has_firstname' => $event->has_firstname,
      'requires_firstname' => $event->requires_firstname,
      'has_email' => $event->has_email,
      'requires_email' => $event->requires_email,
      'has_phone' => $event->has_phone,
      'requires_phone' => $event->requires_phone,
      'has_company' => $event->has_company,
      'requires_company' => $event->requires_company,
      'has_location' => $event->has_location,
      'requires_location' => $event->requires_location,
      'has_address' => $event->has_address,
      'requires_address' => $event->requires_address,
      'has_cost_center' => $event->has_cost_center,
      'has_remarks' => $event->has_remarks,
      'has_meal_options' => $event->has_meal_options,
      'requires_meal_options' => $event->requires_meal_options,
      'meal_options' => [
        'Fleisch' => $event->has_meal_option_meat ? __('Fleisch') : null,
        'Vegetarisch' => $event->has_meal_option_vegetarian ? __('Vegetarisch') : null,
        'Vegan' => $event->has_meal_option_vegan ? __('Vegan') : null,
      ],
      'has_button_additional_individuals' => $event->has_button_additional_individuals,
      'has_field_additional_individual_salutation' => $event->has_button_additional_individuals ? $event->has_field_additional_individual_salutation : false,
      'has_field_additional_individual_email' => $event->has_button_additional_individuals ? $event->has_field_additional_individual_email : false,
      'has_field_additional_individual_name' => $event->has_button_additional_individuals ? $event->has_field_additional_individual_name : false,
      'has_field_additional_individual_firstname' => $event->has_button_additional_individuals ? $event->has_field_additional_individual_firstname : false,
    ]);
  }

  public function register(Request $request)
  {
    $event = Entry::find($request->input('event_id'));

    $validationResult = $this->validateRequest($request, $event);
    if ($validationResult !== TRUE) {
      return $validationResult;
    }

    $slug = $event->title . ' ' . $request->input('firstname') . ' ' . $request->input('name');

    // build data
    $data = [
      'title' => $event->title,
      'event_id' => $event->id,
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
      'wants_meal_options' => $request->input('wants_meal_options'),
      'meal_options' => $request->input('wants_meal_options') && $request->input('meal_options') ? $request->input('meal_options') : null,
      'locale' => $request->input('locale'),
    ];

    // handle additional individuals, build a string out of:
    // name, firstname and meal_options (if available)
    $additional_individuals = [];
    foreach ($request->input('additional_individuals') as $additional_individual)
    {
      // create an array with salutation, email, name, firstname and meal_options
      $additional_individual_data = [
        'salutation' => $additional_individual['salutation'] ?? null,
        'email' => $additional_individual['email'] ?? null,
        'name' => $additional_individual['firstname'] . ' ' . $additional_individual['name'],
        'wants_meal_options' => $additional_individual['wants_meal_options'] ?? false,
        'meal_options' => $additional_individual['meal_options'] ?? 'ohne Essen',
      ];

      // create comma separated string
      $additional_individuals[] = implode(', ', array_filter($additional_individual_data));
    }

    // add newline instead of comma
    $data['additional_individuals'] = implode("\n", $additional_individuals);

    $entry = Entry::make()
      ->collection('event_registrations')
      ->slug($slug)
      ->data($data)
      ->save();
    
    Notification::route('mail', $request->input('email'))
      ->notify(new UserEventRegistrationNotification($data)
    );

    Notification::route('mail', env('MAIL_TO'))
      ->notify(new OwnerEventRegistrationNotification($data)
    );

    return response()->json(['message' => 'Store successful']);
  }

  protected function validateRequest(Request $request, $event)
  {
    $validationRules = $this->getValidationRules($event);

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

  protected function getValidationRules($event)
  {
    $validationRules = [];

    if ($event->has_salutation && $event->requires_salutation) {
      $validationRules['salutation'] = 'required';
    }

    if ($event->has_name && $event->requires_name) {
      $validationRules['name'] = 'required';
    }

    if ($event->has_firstname && $event->requires_firstname) {
      $validationRules['firstname'] = 'required';
    }

    if ($event->has_email && $event->requires_email) {
      $validationRules['email'] = 'required|email|regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    } 

    if ($event->has_phone && $event->requires_phone) {
      $validationRules['phone'] = 'required';
    }

    if ($event->has_company && $event->requires_company) {
      $validationRules['company'] = 'required';
    }

    if ($event->has_location && $event->requires_location) {
      $validationRules['location'] = 'required';
    }

    if ($event->has_address && $event->requires_address) {
      $validationRules['address'] = 'required';
    }

    if ($event->has_meal_options && $event->requires_meal_options) {
      $validationRules['meal_options'] = 'required';
    }

    if ($event->has_button_additional_individuals) {
      $validationRules['additional_individuals.*.name'] = 'required';
      $validationRules['additional_individuals.*.firstname'] = 'required';
      
      if ($event->has_meal_options && $event->requires_meal_options) {
        $validationRules['additional_individuals.*.meal_options'] = 'required_if:additional_individuals.*.wants_meal_options,true';
      }
    }

    $validationRules['toc'] = 'accepted';

    // Set validation messages
    $validationMessages = [];

    if ($event->has_salutation && $event->requires_salutation) {
      $validationMessages['salutation.required'] = __('Anrede ist erforderlich');
    }

    if ($event->has_name && $event->requires_name) {
      $validationMessages['name.required'] = __('Name ist erforderlich');
    }

    if ($event->has_firstname && $event->requires_firstname) {
      $validationMessages['firstname.required'] = __('Vorname ist erforderlich');
    }

    if ($event->has_email && $event->requires_email) {
      $validationMessages['email.required'] = __('E-Mail-Adresse ist erforderlich');
      $validationMessages['email.email'] = __('E-Mail-Adresse muss gültig sein');
      $validationMessages['email.regex'] = __('E-Mail-Adresse muss gültig sein');
    }

    if ($event->has_phone && $event->requires_phone) {
      $validationMessages['phone.required'] = __('Telefonnummer ist erforderlich');
    }

    if ($event->has_company && $event->requires_company) {
      $validationMessages['company.required'] = __('Firma ist erforderlich');
    }
    
    if ($event->has_location && $event->requires_location) {
      $validationMessages['location.required'] = __('Ort ist erforderlich');
    }

    if ($event->has_address && $event->requires_address) {
      $validationMessages['address.required'] = __('Adresse ist erforderlich');
    }

    if ($event->has_meal_options && $event->requires_meal_options) {
      $validationMessages['meal_options.required'] = __('Essen ist erforderlich');
    }

    if ($event->has_button_additional_individuals) {
      $validationMessages['additional_individuals.*.name.required'] = __('Name ist erforderlich');
      $validationMessages['additional_individuals.*.firstname.required'] = __('Vorname ist erforderlich');

      if ($event->has_meal_options && $event->requires_meal_options) {
        $validationMessages['additional_individuals.*.meal_options.required'] = __('Essen ist erforderlich');
      }
    }

    // add message for toc
    $validationMessages['toc.accepted'] = __('Sie müssen die Teilnahme- und Annullationsbedingungen sowie die Datenschutzbestimmungen akzeptieren');

    return [
      'rules' => $validationRules,
      'messages' => $validationMessages,
    ];
  }
}