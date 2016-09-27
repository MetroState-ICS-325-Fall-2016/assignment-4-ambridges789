<?php

// This assumes FormHelper.php is in the same directory as
// this file.
require 'FormHelper.php';

// setup the arrays of choices in the select menus
// these are needed in display_form( ), validate_form( ),
// and process_form( ), so they are declared in the global scope
$sweets = array('puff' => 'Sesame Seed Puff',
                'square' => 'Coconut Milk Gelatin Square',
                'cake' => 'Brown Sugar Cake',
                'ricemeat' => 'Sweet Rice and Meat',
                'icecream' => 'Browns Velvet Vanilla Ice Cream');

$main_dishes = array('cuke' => 'Braised Sea Cucumber',
                     'stomach' => "Sauteed Pig's Stomach",
                     'tripe' => 'Sauteed Tripe with Wine Sauce',
                     'taro' => 'Stewed Pork with Taro',
                     'giblets' => 'Baked Giblets with Salt',
                     'abalone' => 'Abalone with Marrow and Duck Feet',
                     'pizza' => 'Cheese Pizza with a Robust Marinara Sauce'
);

$drinks = array('coke' => 'Coke',
               'diet coke' => "Diet Coke",
               'sprite' => 'Sprite',
               'water' => 'Water',
               'milk' => 'Milk'
);

// The main page logic:
// - If the form is submitted, validate and then process or redisplay
// - If it's not submitted, display
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If validate_form( ) returns errors, pass them to show_form( )
    list($errors, $input) = validate_form();
    if ($errors) {
        show_form($errors);
    } else {
        // The submitted data is valid, so process it
        process_form($input);
    }
} else {
    // The form wasn't submitted, so display
    show_form();
}

function show_form($errors = array()) {
    $defaults = array('delivery' => 'yes',
                      'size'     => 'large');
    // Set up the $form object with proper defaults
    $form = new FormHelper($defaults);

    // All the HTML and form display is in a separate file for clarity
    include 'complete-form.php';
}

function validate_form( ) {
    $input = array();
    $errors = array();

    // name is required
    if (isset($_POST['name'])) {
        $input['name'] = trim($_POST['name']);
    } else {
        $input['name'] = '';
    }
    if (! strlen($input['name'])) {
        $errors[] = 'Please enter your name.';
    }
    // size is required
    if(isset($_POST['size'])) {
        $input['size'] = trim($_POST['size']);
    } else {
        $input['size'] = '';
    }
    if (! in_array($input['size'], ['small','medium','large','xlarge'])) {
        $errors[] = 'Please select a size.';
    }
    // sweet is required
    if (isset($_POST['sweet'])) {
        $input['sweet'] = $_POST['sweet'];
    } else {
        $input['sweet'] = '';
    }
    if (! array_key_exists($input['sweet'], $GLOBALS['sweets'])) {
        $errors[] = 'Please select a valid sweet item.';
    }
    // Drink Required
    if (isset($_POST['drinks'])) {
        $input['drinks'] = $_POST['drinks'];
    } else {
        $input['drinks'] = '';
    }
    if (! array_key_exists($input['drinks'], $GLOBALS['drinks'])) {
        $errors[] = 'Please select a valid drink item.';
    }
    // email required
    if (isset($_POST['email'])) {
        $input['email'] = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    }   else {
        $input['email'] = false;
    }
    if ($input['email']=== false) {
        $errors[] = 'Please enter your email.';
    }
    // exactly two main dishes required
    if (isset($_POST['main_dish'])) {
        $input['main_dish'] = $_POST['main_dish'];
    } else {
        $input['main_dish'] = array();
    }
    if (count($input['main_dish']) != 2) {
        $errors[] = 'Please select exactly two main dishes.';
    } else {
        // We know there are two main dishes selected, so make sure they are
        // both valid
        if (! (array_key_exists($input['main_dish'][0], $GLOBALS['main_dishes']) &&
               array_key_exists($input['main_dish'][1], $GLOBALS['main_dishes']))) {
            $errors[] = 'Please select exactly two valid main dishes.';
        }
    }
    // if delivery is checked, then comments must contain something
    if (isset($_POST['delivery'])) {
        $input['delivery'] = $_POST['delivery'];
    } else {
        $input['delivery'] = 'no';
    }
    if (isset($_POST['comments'])) {
        $input['comments'] = trim($_POST['comments']);
    } else {
        $input['comments'] = '';
    }
    if (($input['delivery'] == 'yes') && (! strlen($input['comments']))) {
        $errors[] = 'Please enter your address for delivery.';
    }

    return array($errors, $input);
}

function process_form($input) {
    // look up the full names of the sweet and the main dishes in
    // the $GLOBALS['sweets'] and $GLOBALS['main_dishes'] arrays
    $sweet = $GLOBALS['sweets'][ $input['sweet'] ];
    $main_dish_1 = $GLOBALS['main_dishes'][ $input['main_dish'][0] ];
    $main_dish_2 = $GLOBALS['main_dishes'][ $input['main_dish'][1] ];
    if (isset($input['delivery']) && ($input['delivery'] == 'yes')) {
        $delivery = 'do';
    } else {
        $delivery = 'do not';
    }
    // build up the text of the order message
    $message=<<<_ORDER_
Thank you for your order, {$input['name']} at {$input['email']}.
You requested the {$input['size']} size of $sweet, $main_dish_1, and $main_dish_2.
You would like a {$input['drinks']} to drink.
You $delivery want delivery.\n
_ORDER_;
    if (strlen(trim($input['comments']))) {
        $message .= 'Your comments: '.$input['comments'];
    }

    // send the message to the chef (don't actually try to send it, uncomment for production):
    # mail('chef@restaurant.example.com', 'New Order', $message);

    // print the message, but encode any HTML entities
    // and turn newlines into <br/> tags
    print str_replace('&NewLine;', "<br />\n", htmlentities($message, ENT_HTML5));
}

