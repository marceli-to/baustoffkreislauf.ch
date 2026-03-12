<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderConfirmation;
use App\Notifications\Order;
use Illuminate\Support\Facades\Validator;

class PublicationController extends Controller
{
  public function get($publicationId)
  {
    $publication = Entry::find($publicationId, 'de');
    return response()->json([
      'publication' => [
        'date' => $publication->date,
        'title' => $publication->title,
        'cost' => $publication->cost,
        'languages' => $publication->languages ?? [],
      ],
    ]);
  }

  public function order(Request $request)
  {
    $publication = Entry::find($request->input('publication_id'));

    $validationResult = $this->validateRequest($request, $publication);
    if ($validationResult !== TRUE) {
      return $validationResult;
    }

    $slug = $publication->title . ' ' . $request->input('firstname') . ' ' . $request->input('name');

    // build data
    $data = [
      'title' => $publication->title,
      'publication_id' => $publication->id,
      'order_date' => now()->format('Y-m-d'),
      'quantities' => $request->input('quantities', []),
      'quantity' => $request->input('quantity'),
      'name' => $request->input('name'),
      'firstname' => $request->input('firstname'),
      'email' => $request->input('email'),
      'phone' => $request->input('phone'),
      'company' => $request->input('company'),
      'invoice_address' => $request->input('invoice_address'),
      'delivery_address' => $request->input('delivery_address') ?? null,
      'remarks' => $request->input('remarks'),
      'locale' => $request->input('locale'),
    ];

    $entry = Entry::make()
      ->collection('orders')
      ->slug($slug)
      ->data($data)
      ->save();
    
    Notification::route('mail', $request->input('email'))
      ->notify(new OrderConfirmation($data)
    );

    Notification::route('mail', 'info@baustoffkreislauf.ch')
      ->notify(new Order($data)
    );

    return response()->json(['message' => 'Store successful']);
  }

  protected function validateRequest(Request $request, $event)
  {
    $hasLanguages = !empty($event->languages);
    $validationRules = $this->getValidationRules($hasLanguages);

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

    if ($hasLanguages) {
      // Ensure at least one language has quantity > 0
      $quantities = $request->input('quantities', []);
      if (is_array($quantities) && array_sum($quantities) < 1) {
        return response()->json(['errors' => [
          'quantities' => __('Bitte mindestens eine Anzahl grösser als 0 angeben'),
        ]], 422);
      }
    }

    return TRUE;
  }

  protected function getValidationRules($hasLanguages = true)
  {
    $validationRules = [];
    if ($hasLanguages) {
      $validationRules['quantities'] = 'present|array';
      $validationRules['quantities.*'] = 'nullable|integer|min:0';
    } else {
      $validationRules['quantity'] = 'required|integer|min:1';
    }
    $validationRules['name'] = 'required';
    $validationRules['firstname'] = 'required';
    $validationRules['email'] = 'required|email|regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    $validationRules['phone'] = 'required';
    $validationRules['invoice_address'] = 'required';

    // Set validation messages
    $validationMessages = [
      'quantities.required' => __('Bitte mindestens eine Sprache mit Anzahl angeben'),
      'name.required' => __('Name ist erforderlich'),
      'firstname.required' => __('Vorname ist erforderlich'),
      'email.required' => __('E-Mail-Adresse ist erforderlich'),
      'email.email' => __('E-Mail-Adresse muss gültig sein'),
      'email.regex' => __('E-Mail-Adresse muss gültig sein'),
      'phone.required' => __('Telefonnummer ist erforderlich'),
      'invoice_address.required' => __('Rechnungsadresse ist erforderlich'),
    ];
    
    return [
      'rules' => $validationRules,
      'messages' => $validationMessages,
    ];
  }
}