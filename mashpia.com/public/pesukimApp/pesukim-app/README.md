# Pesukim App

This is the React front-end for the **Pesukim Site** section of mashpia.com.

## 📁 Folder structure
This app lives inside:

mashpia.com/public/pesukimSite/pesukim-app


When you build it, the final production files (`index.html`, `/static`, etc.) need to go **one level up**, directly into:

mashpia.com/public/pesukimSite/

That’s the folder Apache serves publicly at:

[http://localhost:8080/pesukimSite/](http://localhost:8080/pesukimSite/)

---

## 🧑‍💻 Development

Run locally with:

```bash
npm start
```

The app will open in your browser at http://localhost:3000.

Make all changes here while developing.

🚀 Build for production
When you’re ready to deploy to mashpia.com:

Build the React app:

```bash
npm run build
```

Copy the contents of the build folder into the parent folder (pesukimSite):

```bash
cp -r build/* ..
```

Apache will now serve the new index.html and assets at
http://localhost:8080/pesukimSite/

🧠 Notes
This folder (pesukim-app) is only for development.

The parent folder (pesukimSite) is what the live site actually uses.

The rest of mashpia.com (PHP files, backend connections, etc.) remains untouched.