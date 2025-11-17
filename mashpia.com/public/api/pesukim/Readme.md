# Pesukim App – Backend Contract

This document describes the API the frontend expects.

Base path used by the mock files: `/api/pesukim/`


## 1) GET `/api/pesukim/campaign` — Campaign (Countries & Schools)

### Response (JSON)

```
{
  "countries": [
    {
      "id": "us",
      "name": "United States of America",
      "logo": "/assets/images/flags/cheder-lubavitch-morristown.png",
      "rank": 1,
      "learn": { "goal": 50000, "current": 22000 },
      "recruit": { "goal": 50000, "current": 18000 },
      "pesukimTotal": 123456
    },
    {
      "id": "ru",
      "name": "Russia",
      "logo": "/assets/images/flags/RU.png",
      "rank": 4,
      "learn": { "goal": 50000, "current": 10000 },
      "recruit": { "goal": 50000, "current": 8000 },
      "pesukimTotal": 12345
    },
    {
      "id": "uk",
      "name": "United Kingdom",
      "logo": "/assets/images/flags/UK.png",
      "rank": 2,
      "learn": { "goal": 50000, "current": 12000 },
      "recruit": { "goal": 50000, "current": 9000 },
      "pesukimTotal": 34567
    },
    //...more countries
  ],
  "schools": [
    {
      "id": "ot",
      "name": "Oholei Torah",
      "logo": "/schools/cheder-lubavitch-morristown.png",
      "subtitle": "Brooklyn, NY, United States",
      "rank": 1,
      "learn": { "goal": 20000, "current": 8200 },
      "recruit": { "goal": 8000, "current": 3500 },
      "pesukimTotal": 34567
    },
    //...more schools
  ]
}
```

Notes:
- Countries: logo is optional (right now we aren't displaying flags on the frontend).
- Schools: logo is required. The path must match files in `pesukimApp/pesukim-app/build/`. If you need to add a photo, add it in that folder and afterwards you run `npm run build` in `pesukimApp/pesukim-app`.
- rank controls sort order (ascending → 1 shows first)


## 2) GET `/api/pesukim/prizes` — Prizes Catalog

### Response (JSON)

```
{
  "categories": [
    {
      "name": "SEFORIM",
      "gridClass": "col-4",
      "prizes": [
        {
          "title": "Set of\nIgros Kodesh",
          "img": "/prizes/igros-kodesh.png",
          "alt": "Set of Igros Kodesh",
          "description": "A complete collection of the Rebbe's letters..."
        },
        {
          "title": "Set of English\nShulchon Oruch from Sichos In English",
          "img": "/prizes/set-of-sichos-in-english-shulchon-aroch.png",
          "alt": "Set of English Shulchon Oruch from Sichos In English",
          "description": "The complete Code of Jewish Law translated into English..."
        },
        //...more prizes
      ]
    },
    {
      "name": "TRAVEL",
      "gridClass": "col-2",
      "prizes": [
        {
          "title": "Trip for two\nto Eretz Yisroel\n(2500 value)",
          "img": "/prizes/usa-israel-map.png",
          "alt": "Trip for two to Eretz Yisroel",
          "width": "100px",
          "description": "An amazing opportunity to visit the Holy Land!..."
        },
        {
          "title": "Trip for Two\nanywhere in the\nUnited States\n($1,000 value)",
          "img": "/prizes/usa-israel-map.png",
          "alt": "Trip for Two anywhere in the United States",
          "width": "100px",
          "description": "Plan your perfect getaway!..."
        }
      ]
    },
    {
      "name": "BEIS HAMIKDASH",
      "gridClass": "col-2",
      "prizes": [
        {
          "title": "Model\nBeis Hamikdash",
          "img": "/prizes/rebbe.jpg",
          "alt": "Model Beis Hamikdash",
          "width": "100px",
          "description": "A detailed and educational model of the Holy Temple..."
        },
        {
          "title": "VR Glasses\n+1 year subscription\nto TorahVR",
          "img": "/prizes/tvr-glasses.jpg",
          "img2": "/prizes/torah-vr.png", //if img2 is present it will show 2 images in the prize card
          "alt": "VR Glasses +1 year subscription to TorahVR",
          "width": "90px",
          "height": "90px",
          "description": "Immerse yourself in Torah learning with VR technology!..."
        }
      ]
    },
    //...more sections
  ]
}
```

Notes:
- title supports literal line breaks (\n) for forced line breaks in the card heading.
- Images must exist in `pesukimApp/pesukim-app/build/prizes`.. if you need to add an image there, add it and then run `npm run build` in `pesukimApp/pesukim-app`.


## 3) GET `/api/pesukim/trackers` — Menu Trackers

### Response (JSON)

{
  "learnTeach": { "goal": 50000, "taught": 30000 },
  "armyRecruitment": { "goal": 50000, "recruited": 8000 },
  "pesukim": {
    "date": { "dow": "Monday", "hebrew": "12 Cheshvan", "gregorian": "November 3" },
    "today": 10000,
    "total": 10000
  }
}

Notes:
- Frontend renders two TrackerBar components (red = learn/teach, yellow = recruitment) using these numbers.
- Dates are displayed as text only; backend can return any localized strings.


## 4) POST `/api/pesukim/join-submit.php` — Join Form Submit

### Request (JSON)

The frontend sends a JSON body like:
```
{
  "firstName": "Chaim",
  "lastName": "Levi",
  "dob": "2015-03-14",
  "parentEmail": "parent@example.com",
  "parentPhone": "718-555-1212",
  "referral": "ABC123"
}
```

Notes:
- dob is a native `<input type="date">` value, so it comes through as `YYYY-MM-DD`.
- referral is optional.

### Success Response (201)

Status code: **201 Created**

Body:

```
{
  "ok": true,
  "userId": "usr_123456",
  "next": {
    "checkEmail": true
  }
}
```

Notes:
- userId can be any internal identifier you like (string or number). It is not currently displayed but may be useful in future.
- next is optional and can include any flags you want; frontend only checks that `ok === true` to move to the “Thank you” screen.

### Validation Error Response (422)

Status code: **422 Unprocessable Entity**

Body:

```
{
  "ok": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Fix the fields below",
    "fields": {
      "firstName": "Required",
      "lastName": "Required",
      "dob": "Use YYYY-MM-DD",
      "parentEmail": "Invalid email",
      "parentPhone": "Invalid phone",
      "referral": "Invalid referral code"
    }
  }
}
```

Notes:
- Any subset of the fields key is fine; the frontend maps each field error to the matching input.
- If you want a field-level error, put it under error.fields.<fieldName>.

### Generic Error Response (500 or other 4xx)

Status code: **500** (or appropriate 4xx)

Body:

{
  "ok": false,
  "error": {
    "code": "SERVER_ERROR",
    "message": "Something went wrong. Please try again later."
  }
}

Notes:
- If there is no `error.fields` object, the frontend shows a generic alert with `error.message`.
- Please always return valid JSON with an `ok` boolean, even on errors, so the UI can handle it cleanly.
