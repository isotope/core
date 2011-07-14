===================
ISOTOPE - datatrans
===================

To install the payment module simply copy the folder into system/modules/ and run
a database update. After the update, go to the Isotope config and add a new payment method.

After this please log in your datatrans admin panel and insert the correct url's.
  - URL Post: http://domain.tld/system/modules/isotope/postsale.php
  - URL Success: http://domain.tld/your-checkout-site/step/complete.html
  - URL Error: http://domain.tld/your-checkout-site/step/failed.html


For more security please activate the sign parameter security. Only with this your payment
process is 100% secured. You can generate the sign parameter in the datatrans admin panel.
