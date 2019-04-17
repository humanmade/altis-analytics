# Google Analytics

To install Google Analytics you will need to edit your Tag Manager container and carry out the following steps:

1. Create a new variable of type "Google Analytics Settings"
2. Enter your tracking ID
  - You can optionally configure custom dimensions and metrics under the advanced settings here too, but you will need to create them in Google Analytics first.
3. Create a new tag of type "Google Analytics"
4. Set the "Tracking Type" to "Pageview"
5. Select the variable created in step 1 for the "Google Analytics Settings" value
6. Add a trigger of type "All Pages"
  - You can optionally choose the "Page View" type and set conditions on when the tag fires
