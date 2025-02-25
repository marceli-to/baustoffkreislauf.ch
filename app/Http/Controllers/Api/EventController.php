<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserEventRegistration;
use App\Notifications\OwnerEventRegistration;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
  public function get($eventId)
  {
    $event = Entry::find($eventId, 'de');
    return response()->json([
      'title' => $event->title,
      'has_salutation' => $event->has_salutation,
      'without_charge' => $event->without_charge,
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
      'requires_cost_center' => $event->requires_cost_center,
      'has_party' => $event->has_party,
      'requires_party' => $event->requires_party,
      'has_language' => $event->has_language,
      'requires_language' => $event->requires_language,
      'has_remarks' => $event->has_remarks,
      'has_meal_options' => $event->has_meal_options,
      'meal_options' => [
        'Fleisch' => $event->has_meal_option_meat ? __('Fleisch') : null,
        'Vegetarisch' => $event->has_meal_option_vegetarian ? __('Vegetarisch') : null,
        'Vegan' => $event->has_meal_option_vegan ? __('Vegan') : null,
      ],
      'has_button_additional_individuals' => $event->has_button_additional_individuals,
      'has_field_additional_individual_salutation' => $event->has_field_additional_individual_salutation,
      'has_field_additional_individual_email' => $event->has_field_additional_individual_email,
      'has_field_additional_individual_name' => $event->has_field_additional_individual_name,
      'has_field_additional_individual_firstname' => $event->has_field_additional_individual_firstname,
      'has_field_additional_individual_cost_center' => $event->has_field_additional_individual_cost_center,
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
      'language' => $request->input('language') ?? null,
      'name' => $request->input('name'),
      'firstname' => $request->input('firstname'),
      'email' => $request->input('email'),
      'phone' => $request->input('phone'),
      'company' => $request->input('company'),
      'zip' => $request->input('zip'),
      'city' => $request->input('city'),
      'address' => $request->input('address'),
      'remarks' => $request->input('remarks'),
      'cost_center' => $request->input('cost_center'),
      'language' => $request->input('language'),
      'party' => $request->input('party'),
      'wants_meal_options' => $request->input('wants_meal_options'),
      'meal_options' => $request->input('wants_meal_options') != "false" && $request->input('meal_options') ? $request->input('meal_options') : 'ohne Essen',
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
        'meal_options' => $additional_individual['meal_options'] ?? 'ohne Essen',
        'cost_center' => $additional_individual['cost_center'] ?? null,
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
      ->notify(new UserEventRegistration($data)
    );

    Notification::route('mail', env('MAIL_TO'))
      ->notify(new OwnerEventRegistration($data)
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
      // $validationRules['location'] = 'required';
      $validationRules['zip'] = 'required';
      $validationRules['city'] = 'required';
    }

    if ($event->has_address && $event->requires_address) {
      $validationRules['address'] = 'required';
    }

    if ($event->has_cost_center && $event->requires_cost_center) {
      $validationRules['cost_center'] = 'required';
    }

    if ($event->has_party && $event->requires_party) {
      $validationRules['party'] = 'required';
    }

    if ($event->has_language && $event->requires_language) {
      $validationRules['language'] = 'required';
    }

    if ($event->has_meal_options) {
      $validationRules['wants_meal_options'] = 'required';
      $validationRules['meal_options'] = 'required_if:wants_meal_options,true';
    }

    if ($event->has_button_additional_individuals) {
      if ($event->has_field_additional_individual_name) {
        $validationRules['additional_individuals.*.name'] = 'required';
      }

      if ($event->has_field_additional_individual_firstname) {
        $validationRules['additional_individuals.*.firstname'] = 'required';
      }

      if ($event->has_field_additional_individual_email) {
        $validationRules['additional_individuals.*.email'] = 'required|email|regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
      }

      if ($event->has_field_additional_individual_cost_center) {
        $validationRules['additional_individuals.*.cost_center'] = 'required';
      }
    }

    if ($event->has_button_additional_individuals && $event->has_meal_options) {
      $validationRules['additional_individuals.*.wants_meal_options'] = 'required';
      $validationRules['additional_individuals.*.meal_options'] = 'required_if:additional_individuals.*.wants_meal_options,true';
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
      'party.required' => __('Partei/Verband/Organisation ist erforderlich'),
      'language.required' => __('Sprache ist erforderlich'),
      'meal_options.required' => __('Essen ist erforderlich'),
      'wants_meal_options.required' => __('Angabe ist erforderlich'),
      'additional_individuals.*.name.required' => __('Name ist erforderlich'),
      'additional_individuals.*.firstname.required' => __('Vorname ist erforderlich'),
      'additional_individuals.*.email.required' => __('E-Mail-Adresse ist erforderlich'),
      'additional_individuals.*.email.email' => __('E-Mail-Adresse muss gültig sein'),
      'additional_individuals.*.email.regex' => __('E-Mail-Adresse muss gültig sein'),
      'additional_individuals.*.meal_options.required' => __('Essen ist erforderlich'),
      'additional_individuals.*.cost_center.required' => __('Kostenstelle ist erforderlich'),
      'additional_individuals.*.wants_meal_options.required' => __('Angabe ist erforderlich'),
      'toc.accepted' => __('Sie müssen die Teilnahme- und Annullationsbedingungen sowie die Datenschutzbestimmungen akzeptieren'),
    ];
    
    return [
      'rules' => $validationRules,
      'messages' => $validationMessages,
    ];
  }
}