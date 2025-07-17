=== Student Discount for WooCommerce ===
Contributors: mrvanes, michellewilliamsgeant
Tags: inacademia, student validation, student discount, student, discount
Requires at least: 6.0
Tested up to: 6.8.1
Stable tag: 1.0.3
Requires PHP: 8.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Provides access to a student validation service, using WooCommerce Coupons to apply a discount at the cart for qualifying customers.

== Introduction ==
[youtube https://www.youtube.com/watch?v=wV5WSYthuKk]

Licensed under GPL 3.0 or later.

Brands all over the world offer discounts to students as a strategy to increasing sales, improving conversion rates, attracting new audiences and creating loyalty. The student community has vast spending potential and numerous polls and surveys have found that students lean toward brands that offer them discounts, and the availability of a discount will influence their decision to buy.

Student Discount for WooCommerce is powered by [InAcademia](https://inacademia.org) and brings you all the advantages of offering discounts to the student community without the challenges. Our simple plugin is the real-time, digital equivalent of asking a student to show you their university or student card. It allows you to instantly validate at the shopping cart if a customer is a student* without the need for collecting any additional data or waiting for inefficient offline processes such as document verification.

The Student Discount for WooCommerce plugin adds an ‘I’m a Student’ button or notice to your store’s shopping cart, giving your customers the opportunity to demonstrate their student affiliation. Clicking the ‘I’m a Student’ button or notice links to the InAcademia service that sends a secure authentication request to the student’s institutional identity management service, and requests that they log in with academic credentials already assigned to them. This returns a simple attribute to assert their academic affiliation. If the attribute released is the ‘student’ affiliation, then the user is validated and a discount can be automatically applied to the shopping cart, based on a pre-configured discount coupon defined by you using standard WooCommerce functionality. This means you can offer meaningful discounts to real students without having to request and store additional personal data.

The whole validation process takes seconds and is based on the trusted [eduGAIN](https://edugain.org) academic federated identity infrastructure.

The Student Discount for WooCommerce plugin is free to download and comes with a 14-day free trial for access to the InAcademia service. Continued use after the trial will require a subscription with InAcademia, where a small monthly fee will entitle you to up to 100, 500 or 1000 student validations per month. For more information about our packages, please visit [https://inacademia.org/shop](https://inacademia.org/shop).

\* At institutions that have joined an academic identity federation that is a member of the [eduGAIN](https://edugain.org) interfederation.

== Funding ==
![EU-Funded Logo](https://wiki.geant.org/download/attachments/725614690/image-2024-1-8_18-16-12.png)
GN5-1 project is funded from the Horizon Europe research and innovation programme under Grant Agreement No. 101100680 (GN5-1)

== Getting started ==
**Step one: configure the discount to be applied**
Set up your discount using the Coupon feature offered by the [WooCommerce Marketing feature set](https://woocommerce.com/document/coupon-management/) by creating an appropriately named coupon that defines the extent of the discount that you wish to offer and enter the coupon name in the box labelled 'Coupon'. If you wish to change the Coupon you will need to overwrite the data with the new Coupon Code in the field labelled 'Coupon'.

**Step two: set up your subscription and make it unique to the plugin in your shop**
You will need to visit [https://inacademia.org/shop](https://inacademia.org/shop) to complete your subscription to the InAcademia Service in order to receive a unique client_id and client_secret, and it is necessary to link your subscription with the plugin in two stages before the I'm a Student button will be available for users to interact with. When you install the plugin, a unique redirect_uri is created on the Setting tab. This value must be entered when prompted, when processing your subscription order.

**Step three: link your subscription to the plugin**
Your client_id and client_secret will automatically be created during the Subscription order process. You will find them in the Subscription Details of the 'My Account' section of your WooCommerce account; they are both vital terms that are required for the proper-functioning of the service and will be transmitted to the InAcademia service with each user's validation request. You must paste them to the correct boxes in the Settings tab.

**Step four: activate your service**
When you have created your discount coupon, linked your redirect_uri to your subscription, and linked the client_id and client_secret to the plugin, you will need to decide how you would like to invite users to validate their academic affiliation, either by using a Notice URL or by hitting the 'I'm a Student' button.

It's allowable to use either or both, but please be aware that if you check either box, either the 'I'm a Student' button or 'I'm a Student' notice will be enabled on your shopping cart. Ensure that your subscription is complete and active before hitting 'Save Settings'.

== License ==
This software is licensed under the GPLv3 or later. For more details, see the full [GPL-3.0](https://www.gnu.org/licenses/gpl-3.0.en.html) license.

== Contact ==
For more information or support, please visit our [plugin support page](https://inacademia.org/plugin-support/).

== Privacy Policy ==
The Student Discount for WooCommerce InAcademia subscription service privacy policy is [here](https://inacademia.org/student-discount-for-woocommerce-inacademia-subscription-service-privacy-policy/).

== Copyright ==
Copyright (c) 2023-2024 GÉANT Association on behalf of the GN5-1 project
[https://github.com/InAcademia/student-discount-for-woocommerce/blob/main/COPYRIGHT](https://github.com/InAcademia/student-discount-for-woocommerce/blob/main/COPYRIGHT)

== Changelog ==
= 1.0.3 =
- Bump phpseclib
- Tested against WordPress 6.8.1
- Tested against WooCommerce 10.0.2

= 1.0.2 =
- Enable TLS verification

= 1.0.1 =
- Provides human-friendly error descriptions when cart discount cannot be applied
- Tested against WordPress 6.7.1
- Tested against WooCommerce 9.5.2

= 1.0 =
* First release

== Dependencies ==
Dependency openid-connect-php-v1.0.2
- Version: v1.0.2
- URL: [https://github.com/jumbojett/openid-connect-php](https://github.com/jumbojett/openid-connect-php)
- Licence: Apache 2.0
- Copyright MITRE 2020

Dependency paragonie/constant_time_encoding-v3.0.0
- Version: v3.0.0
- URL:   [https://github.com/paragonie/constant_time_encoding.git](https://github.com/paragonie/constant_time_encoding.git)
- Licensed: MIT
- Copyright 2014 Steve Thomas, Copyright 2016-2022 Paragon Initiative Enterprises

Dependency paragonie/random_compat-v9.99.100
- Version: v9.99.100
- URL:  [https://github.com/paragonie/random_compat.git](https://github.com/paragonie/random_compat.git)
- Licensed: MIT
- Copyright 2015 Paragon Initiative Enterprises

Dependency phpseclib-3.0.46
- Version: 3.0.46
- URL: [https://github.com/phpseclib](https://github.com/phpseclib)
- Licensed: MIT
- Copyright 2011-2019 TerraFrost and other contributors

[InAcademia](https://inacademia.org/) is a GÉANT service
[Contact](https://inacademia.org/plugin-support/)
[Privacy Statement](https://inacademia.org/privacy-statement/)
