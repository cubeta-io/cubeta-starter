<!-- docs/CHANGELOG.md -->

# **Changelog :**

## **V 5.1.0**

### **What's new**

1. You can now choose how the generated code validates the incoming requests data. While installing
   (`php artisan cubeta:install api` , `web` or `react-ts`) or from the GUI settings page you pick one of:
    - `FormRequest` : the classic Laravel form requests (the default, and what previous versions always generated)
    - `DTO` : validated DTOs built on top of [wendelladriel/laravel-validated-dto](https://github.com/WendellAdriel/laravel-validated-dto)
    - `Both` : generate both classes
   Your choice is stored in `cubeta-starter.config.json` under `validation_type` , so every later generation follows it.
2. Introducing the DTO generator, which creates a
   `App\DTOs\<version>\<Model>\StoreUpdate<Model>DTO` class extending `ValidatedDTO` with:
    - the same validation rules the form request gets
    - typed properties inferred from the column types (`int` , `float` , `bool` , `string` , `UploadedFile`),
      nullable columns being nullable and defaulting to `null`
    - casts for the castable columns (`IntegerCast` , `FloatCast` , `BooleanCast` , `StringCast`)
3. Introducing the `create:dto` command and a matching `--dto` option for `create:model` , alongside the existing
   `create:request` / `--request` .
4. `wendelladriel/laravel-validated-dto` is now installed for you by the api , web and react-ts installers whenever the
   chosen validation type includes DTOs.
5. New `dto_namespace` and `dto_path` keys in `config/cubeta-starter.php` to control where the generated DTOs live.
6. The generated controllers now inject the validation class you chose : a DTO is injected and read with
   `$dto->toArray()` , while a form request is still injected and read with `$request->validated()` . When you pick
   `Both` , both classes are generated and the controllers depend on the form request.

### **What has been improved**

1. The generated unique column rule now uses `request()->isMethod('PUT')` and `request()->route(...)` instead of
   `$this->isMethod(...)` and `$this->route(...)` , so the same rule works inside form requests and DTOs alike.

## **V 5.0.2**

### **What's new**

1. Introducing a new `FilepondInput` file field for the React/Inertia dashboard, built on top of
   `FilePond`, which replaces the previous plain file `Input` used for file columns. It comes with:
    - Drag-and-drop uploads with image previews and file posters
    - Single or multiple file selection via the `isMultiple` prop
    - Accepted file-type restrictions via the `acceptedFileTypes` prop (defaults to common image types)
    - Automatic hydration of existing `Media` values on edit forms, so already-uploaded files show up in
      the field
    - Standard field styling, label and validation error handling consistent with the other form fields
2. File columns now generate the `FilepondInput` component instead of a basic file input, and edit forms
   pre-fill the field with the current media.
3. The required FilePond plugins (image preview, file poster, file-type validation and EXIF orientation)
   are registered automatically in the dashboard layout, and all `filepond` / `react-filepond` packages
   are installed automatically by the React/TS packages installer.

## **V 5.0.1**

### **What's new**

1. Introducing a brand-new rich-text editor for the React/Inertia dashboard built on top of `Tiptap`, replacing the
   previous editor setup. The editor ships with a full toolbar out of the box:
    - Text formatting: bold, italic, text color and font family
    - Block elements: headings (H1–H6), blockquotes, bullet and ordered lists, horizontal rules
    - Rich content: links, images (by URL), tables, YouTube embeds and multi-column layouts
    - A live footer showing character and word counts
    - Debounced auto-save so content is committed to the form without an explicit save on every keystroke
    - An `extraButtons` prop so you can inject your own toolbar buttons with access to the editor instance
2. Introducing the `TextEditor` form field that wraps the Tiptap editor with the standard field styling, label, required
   indicator and validation error handling used across the other form fields.
3. Introducing the `TranslatableTextEditor` component for rich-text content that needs to be translated across multiple
   languages, following the same pattern as the other translatable form fields and integrating with the
   `FormLocaleProvider` language switcher.
4. All required `@tiptap/*` packages are now installed automatically by the React/TS packages installer, so the editor
   works with no extra setup.

## **V 5.0.0**

This is a major release that upgrades the whole stack to `Laravel 13` and rebuilds the generated React

dashboard on top of `shadcn/ui`. Because of the framework bump and the frontend rewrite it is a breaking

change, so we made it a major version.

### **What's new**

1. Adding support for `Laravel 13` and dropping support for previous versions (now requires `PHP 8.3+`).
2. The generated React/Inertia dashboard is now built on top of `shadcn/ui` , so you get a full set of accessible,
   themeable UI primitives (`dialog` , `drawer` , `sidebar` , `select` , `calendar` , `popover` ,
   `dropdown-menu` , `tabs` , `card` , `chart` and more) published straight into your project.
3. A `components.json` file is now generated so you can pull additional `shadcn` components into your project using the
   `shadcn` CLI.
4. Introducing light/dark mode out of the box through a new `theme-provider` (powered by `next-themes`) with a
   ready-made theme toggle in the dashboard header.
5. Toast notifications now use `sonner` via a dedicated `toaster-provider` , replacing the old
   `react-toastify` / `sweetalert2` setup.
6. Introducing the `SerializedMedia` serializer and a new `MediaValidationRule` to standardize how uploaded files are
   validated and represented across requests, resources and the frontend.
7. New form field components generated for the dashboard, including a `date-picker` (backed by
   `react-day-picker`), `checkbox` , `radio` , `textarea` and reworked translatable inputs.
8. A new `FormLocaleProvider` replaces the old translatable inputs context to manage the active locale of translatable
   fields inside your forms.

### **What has been improved**

1. Upgraded the frontend toolchain: `Inertia React 3` , `Vite 8` , `TypeScript 7` and `Tailwind CSS 4.3` , with
   `lucide-react` icons replacing the hand-written icon components.
2. Generated Models now use the `#[Fillable]` attribute instead of the `$fillable` array, matching Laravel 13
   conventions and keeping model classes cleaner.
3. Auth flashes and redirects now use Inertia flash data instead of session flashes for a smoother SPA experience, and
   the auth screens ship with a dedicated `AuthLayout` .
4. The `config/cubeta-starter.php` paths now use `join_paths()` so generation works consistently across operating
   systems.
5. Command execution inside the generator now runs through `proc_open` instead of `shell_exec` , capturing both stdout
   and stderr and streaming output in real time for more reliable installs.
6. Improved log handling and caching in the generation engine for more reliable and readable output.
7. Added richer static-analysis annotations (`#[ExpectedValues]` , `#[FileReference]`) across the source for a better
   developer experience when contributing to or extending the package.
8. Standardized all generated frontend file and import paths to lowercase for consistency.

### **Fixing some bugs**

1. Fixed `ApiSelect` typing and its handling of multiple-selection updates.
2. Fixed media handling and file serialization edge cases in the generated resources and casts.
3. Various fixes across the generated Inertia pages, auth screens and dashboard components.

### **Notes / breaking changes**

1. The `cubeta-starter-templates` publish tag and its template-customization workflow have been removed.
2. The `policy_path` / `policy_namespace` config entries were removed from `config/cubeta-starter.php` .
3. Because of the `shadcn/ui` rewrite, previously published dashboard components live under new lowercase paths , so
   re-publish the frontend assets when upgrading an existing project.

## **V 4.0.0**

This version basically is a rewrite for the package and it was a massive rewrite so for that we make it a major

version update

1. Fixed bugs in blade and react components
2. Add more type safety to the generated code
3. Improved class doc blocks
4. Add prettier to format JSX and Blade files
5. Introduce the new `Http` class in Inertia React stack to normalize the request handling
6. Refactor MainTestCase class and TestHelpers trait to be able to chain the calls in your testing

## **V 3.0.0**

1. Adding support for the `Laravel 12` and drop support for previous versions.
2. Adding support for `inertia.js 2.0` and drop support for previous versions.
3. Adding support for `tailwindcss 4.0` and drop support for previous versions.
4. Adding support for `react 19` and drop support for previous versions.
5. Introduce `ApiResponse` class to replace the `RestTrait` to handle api responses
6. Introduce the new `MediaCast` mode cast class to replace the usage of the `FileHandler` trait
7. Bug fixes

## **V 2.2.0**

1. Adding support for the `Laravel 11` and drop support for previous versions.
2. Bug fixes

## **V 2.1.0**

1. Introducing The `BaseBulkAction` class to make it easy to define your model available bulk actions .
2. Removing The BaseService and BaseRepository classes interfaces .

## **V 2.0.0**

1. Adding support generate dashboard CRUD'S using React , Typescript , Inertia.js , Tailwindcss
2. Adding the ability to generate the code structure based on a version number via the package config
   file [check here](usage.md#config)
3. Updating the structure of the repository and service patterns to be instantiated using the singleton pattern and
   remove their dependency injection process
4. Now just the blade based generation requires a service provider to register the blade components published via the
   package
5. All required middlewares , exceptions handlers and service providers for a specific stack is being registered via its
   install command

## **V 1.1.7**

### **fixing some bugs**

1. fixing the generated routes names for the generated controllers .
2. now when publishing for api or for web 2 route files will be generated ( `public` , `protected` ) to give more
   customizable routing instead of the generated route files based on the actor name .

## **V 1.1.6**

1. new attribute `exact` has been added to the `gallery-item` blade component so if you'd like to provide the exact
   source of the image
2. add 2 new components `<x-translatable-text-editor />` and `<x-translatable-long-text-field />` to handle the
   localized long text field (the ones those need text editors)
3. there is now three function for sweet alert messages `triggerSwallSuccess(message)` , `triggerSwalError(message)` ,
   `triggerSwallMessage(message)` so you can use them in your project after including `public/js/CustomFunctions.js` ,
   (in the generated dashboard this file is already included).
4. now there is a `cubeta-starter.config.json` in the root directory of your project this will guid the package for some
   tables data, and it is a first step on the next feature which is (presets handling) .
5. we've added `create:example` command for those who are trying to se what the package capable of , this command will
   give you three option :
    - the first one is to create the Category model which has many products
    - the second one is to create the Product model which related to brands by many to many relation
    - finally the brands model
6. we've tried to not generate a code that use a relation to a table not defined yet and then when it is defined we add
   the proper code (you can check that by running the command : `php artisan create:example` and generate the product
   before the category) .

### **fixing some bugs**

1. remove `required` attribute from the input fields in the generated _edit_ forms
2. adding `.gallery` css class to the `gallery-item` blade component so the functions in the
   `public/js/PluginInitializer.js` file can handle it.

## **V 1.1.5**

1. Now every input component has a `name` attribute this attribute is nullable, when it is `null` this attribute will
   take the value of the `label` attribute transformed to **lower** _and_ **snake case**

## **V 1.1.4**

1. Making the columns of type translatable being cast into `Translatable::class` custom cast instead of generating an
   accessor for each translated column . you can find the custom cast `Translatable::class` after publishing
   `cubeta-starter-locale` tag in the `app/Casts` directory

