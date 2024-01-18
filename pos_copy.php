
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Multi step Sign up form</title>
    <link
      href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css"
      rel="stylesheet"
    />
  </head>

  <body>
    <div class="flex items-center min-h-screen bg-gray-50">
      <div
        class="flex-1 h-full max-w-4xl mx-auto bg-white rounded-lg shadow-xl"
      >
        <div class="flex flex-col md:flex-row">
          <div class="h-32 md:h-auto md:w-1/2">
            <img
              class="object-cover w-full h-full"
              src="https://cdn.pixabay.com/photo/2021/01/15/17/01/green-5919790__340.jpg"
              alt="img"
            />
          </div>
          <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
            <div class="w-full">
              <h6 class="mb-4 text-sm">Step1/3</h6>
              <h3 class="mb-4 text-xl font-bold text-blue-600">Sign up</h3>
              <div class="flex flex-wrap mx-auto">
                <a
                  class="
                    inline-flex
                    items-center
                    justify-center
                    w-1/2
                    py-3
                    font-medium
                    leading-none
                    tracking-wider
                    text-indigo-500
                    bg-gray-100
                    border-b-2 border-indigo-500
                    rounded-t
                    sm:px-6 sm:w-auto sm:justify-start
                  "
                >
                  STEP 1
                </a>
                <a
                  class="
                    inline-flex
                    items-center
                    justify-center
                    w-1/2
                    py-3
                    font-medium
                    leading-none
                    tracking-wider
                    border-b-2 border-gray-200
                    sm:px-6 sm:w-auto sm:justify-start
                    hover:text-gray-900
                  "
                >
                  STEP 2
                </a>
                <a
                  class="
                    inline-flex
                    items-center
                    justify-center
                    w-1/2
                    py-3
                    font-medium
                    leading-none
                    tracking-wider
                    border-b-2 border-gray-200
                    sm:px-6 sm:w-auto sm:justify-start
                    hover:text-gray-900
                  "
                >
                  STEP 3
                </a>
              </div>
              <div class="mt-4 mb-4">
                <label class="block text-sm"> First Name </label>
                <input
                  type="text"
                  class="
                    w-full
                    px-4
                    py-2
                    text-sm
                    border
                    rounded-md
                    focus:border-blue-400
                    focus:outline-none
                    focus:ring-1
                    focus:ring-blue-600
                  "
                  placeholder="First Name"
                />
              </div>
              <div class="mb-4">
                <label class="block mb-2 text-sm"> Last Name </label>
                <input
                  type="text"
                  class="
                    w-full
                    px-4
                    py-2
                    text-sm
                    border
                    rounded-md
                    focus:border-blue-400
                    focus:outline-none
                    focus:ring-1
                    focus:ring-blue-600
                  "
                  placeholder="Last Name"
                />
              </div>
              <div class="mb-4">
                <label class="block mb-2 text-sm"> Phone Number </label>
                <input
                  class="
                    w-full
                    px-4
                    py-2
                    text-sm
                    border
                    rounded-md
                    focus:border-blue-400
                    focus:outline-none
                    focus:ring-1
                    focus:ring-blue-600
                  "
                  placeholder="Phone Number"
                  type="number"
                />
              </div>
              <div class="flex justify-end">
                <button
                  class="
                    px-6
                    py-2
                    mt-4
                    text-sm
                    font-medium
                    leading-5
                    text-center text-white
                    transition-colors
                    duration-150
                    bg-blue-600
                    border border-transparent
                    rounded-lg
                    hover:bg-blue-700
                    focus:outline-none
                  "
                  href="#"
                >
                  Next
                </button>
              </div>
              <div class="mt-4 text-center">
                <p class="text-sm">
                  Already have account
                  <a href="#" class="text-blue-600 hover:underline">
                    Sign in.</a
                  >
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>