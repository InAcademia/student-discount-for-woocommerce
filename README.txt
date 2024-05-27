=== InAcademia Student Validation ===
Contributors: MP van Es
Tags: inacademia, student validation, student discount
Requires at least: 6.0
Tested up to: 6.3.3
Stable tag: 4.3
Requires PHP: 8.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Provides access to an online student validation service, using WooCommerce Coupons to apply a defined discount at checkout for qualifying customers.

# wc-inacademia

Licensed under GPL 3.0 or later.

Brands all over the world offer discounts to students as a means of increasing sales, improving conversion rates, attracting new audiences and creating loyalty. The student community has vast spending potential and numerous polls and surveys have found time and time again that students lean toward brands that offer them discounts, and the availability of a discount will influence their decision to buy.

Student Discount for WooCommerce is powered by InAcademia and brings you all the advantages of offering discounts to the student community without the challenges. Our simple plugin is the real-time, digital equivalent of asking a student to show you their university or student card. It allows you to instantly validate at the checkout if a customer is a student* without the need for collecting any additional data or waiting for inefficient offline processes such as document verification.

The Student Discount for WooCommerce plugin adds an ‘I’m a Student’ button or notice to your store’s checkout giving your customers the opportunity to demonstrate their student affiliation. Clicking the ‘I’m a Student’ button or notice links to the InAcademia service that sends a secure authentication request to the student’s institutional identity management service, and requests that they login with  academic credentials already assigned to them. This returns a simple attribute to assert their academic affiliation. If the attribute released is the ‘student’ affiliation, then the user is validated and a discount can be automatically applied to the shopping cart, based on a pre-configured discount coupon defined by you using standard WooCommerce functionality. This means you can offer meaningful discounts to real students, without having to request and store additional personal data.

The whole validation process takes seconds and is based on the trusted eduGAIN academic federated identity infrastructure.

Student Discount for WooCommerce is free to download and comes with a 14-day free trial for access to the InAcademia service. Continued use after the trial will require a subscription with InAcademia at a cost of €250 per month which will entitle you to up to 1000 validations per month.

*at institutions that have joined an academic identity federation that is a member of the eduGAIN interfederation.

Co-funded by the European Union

## Installation instructions

Clone this repo in an existing WordPress + WooCommerce installation under ```wp-content/plugins/```

Enable the plugin in WP admin console

Create InAcademia coupon in WooCommerce

Configure coupon and OIDC client_id and secret in inacademia admin page

## Getting started

### Step one: configure the discount to be applied

Set up your discount using the Coupon feature offered by the WooCommerce Marketing feature set and enter it in the box labelled 'Coupon'. If you wish to change the Coupon you will need to overwrite the data with the new Coupon Code in the field labelled 'Coupon'.

### Step two: set up your subscription and make it unique to the plugin in your shop

You will need to visit [https://inacademia.org/shop](https://inacademia.org/shop) to complete your subscription to the InAcademia Service in order to receive a unique client_id and client secret, and it is necessary to link your subscription with the plugin in two stages before the InAcademia button will be available for users to interact with. When you install the plugin, a unique redirect_uri is created on the Setting tab. This value must be entered when prompted, when processing your subscription order.

### Step three: link your subscription to the plugin

Your client_id and client_secret will be automatically created during the Subscription order process. You will find them in the Subscription Details of the 'My Account' section of your WooCommerce account; they are both vital terms that are required for the proper-functioning of the service and will be transmitted to the InAcademia service with each user's validation request. You must paste them to the correct boxes in the Settings tab.

### Step four: activate your service

When you have created your discount coupon, linked your redirect_uri to your subscription, and linked the client_id and client_secret to the plugin, you will need to decide how you would like to invite users to validate their academic affiliation, either by using a Notice URL or by hitting the 'InAcademia' button.

It's allowable to use either or both, but please be aware that if you check either box, either the 'I'm a Student' button or 'I'm a Student notice' will be enabled on your shopping cart. Ensure that your subscription is complete and active before hitting 'Save Settings'.

To access support, please visit [https://inacademia.org/plugin-support/](https://inacademia.org/plugin-support/)


== Dependencies ==

Dependency openid-connect-php-v0.9.10
- Version: v0.9.10
- URL: [https://github.com/jumbojett/openid-connect-php](https://github.com/jumbojett/openid-connect-php)
- Licence: Apache 2.0
- Copyright (c) [yar] Jumbojett

Dependency paragonie/constant_time_encoding-v2.6.3
- Version: v2.6.3
- URL:   [https://github.com/paragonie/constant_time_encoding.git](https://github.com/paragonie/constant_time_encoding.git)
- Licensed: MIT
- Copyright 2014 Steve Thomas, Copyright 2016-2022 Paragon Initiative Enterprises

Dependency paragonie/random_compat-v9.99.100
- Version: v9.99.100
- URL:  [https://github.com/paragonie/random_compat.git](https://github.com/paragonie/random_compat.git)
- Licensed: MIT
- Copyright 2015 Paragon Initiative Enterprises

Dependency phpseclib-3.0.19
- Version: 3.0.19
- URL: [https://github.com/phpseclib](https://github.com/phpseclib)
- Licensed: MIT
- Copyright 2011-2019 TerraFrost and other contributors

Privacy Policy: [https://inacademia.org/student-discount-for-woocommerce-inacademia-subscription-service-privacy-policy/](https://inacademia.org/student-discount-for-woocommerce-inacademia-subscription-service-privacy-policy/)

Copyright (c) 2023-2024 GÉANT Association on behalf of the GN5-1 project
[https://gitlab.geant.org/inacademia/wc-inacademia/-/blob/main/COPYRIGHT](https://gitlab.geant.org/inacademia/wc-inacademia/-/blob/main/COPYRIGHT)
