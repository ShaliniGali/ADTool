# JS Unit Tests

Objective: To update our JS so that it can run as client-side JS and is testable as server-side JS

# Should be running from assets

1. Build the image used to run JS tests

    ```bash
    docker build -t js-test-runner ./
    ```

2. Ensure the container can write to `./coverage`

    ```bash
    sudo chmod -R 777 js/tests/coverage
    ```

3. Run tests to produce reports in `./coverage` (**Note you may need to provide values for `source`**)

    ```bash
    docker run --mount type=bind,source="$(pwd)",target="/home/node/js" --mount type=bind,source="$(pwd)/js/tests/coverage",target="/home/node/coverage" js-test-runner npm run test:unit
    ```

  4. If tests are handing up clear the contents of the following folder paths
    - assets/js/dist
    - assets/node_modules
    
For NON-DOCKER users

1. Run tests to produce reports in `./coverage`
  For all tests:
  ```bash
    npm run test
  ```
  For one test
  ```bash
    npm run test -- example.test.js
  ```

## Matchers (https://jestjs.io/docs/using-matchers)


Truthiness
In tests, you sometimes need to distinguish between undefined, null, and false, but you sometimes do not want to treat these differently. Jest contains helpers that let you be explicit about what you want.

- toBeNull matches only null
- toBeUndefined matches only undefined
- toBeDefined is the opposite of toBeUndefined
- toBeTruthy matches anything that an if statement treats as true
- toBeFalsy matches anything that an if statement treats as false

For example:

```
test('null', () => {
  const n = null;
  expect(n).toBeNull();
  expect(n).toBeDefined();
  expect(n).not.toBeUndefined();
  expect(n).not.toBeTruthy();
  expect(n).toBeFalsy();
});

test('zero', () => {
  const z = 0;
  expect(z).not.toBeNull();
  expect(z).toBeDefined();
  expect(z).not.toBeUndefined();
  expect(z).not.toBeTruthy();
  expect(z).toBeFalsy();
});
```

All other matchers: https://jestjs.io/docs/expect


## Organizing tests

describe breaks your test suite into components. Depending on your test strategy, you might have a describe for each function in your class, each module of your plugin, or each user-facing piece of functionality.

You can also nest describes to further subdivide the suite.

it is where you perform individual tests. You should be able to describe each test like a little sentence, such as "it calculates the area when the radius is set". You shouldn't be able to subdivide tests further-- if you feel like you need to, use describe instead.
```
describe('Circle class', function() {
  describe('area is calculated when', function() {
    it('sets the radius', function() { ... });
    it('sets the diameter', function() { ... });
    it('sets the circumference', function() { ... });
  });
});
```

describe is for grouping, it is for testing.

As the jest docs says, test and it are the same: https://jestjs.io/docs/en/api#testname-fn-timeout

test(name, fn, timeout)

Also under the alias: it(name, fn, timeout)

and describe is just for when you prefer your tests to be organized into groups: https://jestjs.io/docs/en/api#describename-fn

describe(name, fn)

describe(name, fn) creates a block that groups together several related tests. For example, if you have a myBeverage object that is supposed to be delicious but not sour, you could test it with:
```
const myBeverage = {
  delicious: true,
  sour: false,
};

describe('my beverage', () => {
  test('is delicious', () => {
    expect(myBeverage.delicious).toBeTruthy();
  });

  test('is not sour', () => {
    expect(myBeverage.sour).toBeFalsy();
  });
});
```

## TODO

- [x] The project ideally should have 1 `package.json`/`package-lock.json` with testing dependencies saved as development dependencies.
- [x] How to place tests without duplicating files?
- [ ] How to allow the container to write to `./coverage`?
- [ ] How do `console.log`s affect test execution?

##  JS Test Implementation Details

### Required Changes to Make Existing JS Code Testable Server-side

- [x] How do we run functions that are not exported?
    - Defined functions should be either called or attached as a handler in the same script.
- [x] How do we run classes that are not exported?
	- Exported and non-exported classes can be tested using Jests' spyOn() function.
	- More info on that and general Jest testing here:
	  https://dev.to/dstrekelj/how-to-test-classes-with-jest-jif
- [] How do we mock post/get requests

### Testing Implementation How-tos

- [x] How do we include native JS (such as `window`)?
    - `jest` ships with `jsdom`, which injects those objects
- [x] How do we include global dependencies (such as `jQuery` or functions defined in other scripts)?
    - Global deps should be assigned to the global object
- [x] How do we mock object methods (such as `$.post`)?
    - Depending on the nature of the object, the property can be reassigned or the object's prototype can be modified
- [x] How do we include jQuery plugins (such as `select2`)?
    - Use `require` to include the file needed to attach the plugin (see project root `webpack.config.js`)
- [ ] Are there setUp, tearDown methods (ex: adding/removing globals)?

## References

- Testing jQuery via side-effects: https://jestjs.io/docs/tutorial-jquery
- Mocking functions for inspection: https://jestjs.io/docs/mock-functions
- Configuring Jest to use jsdom by default (injects native client-side JS objects) https://jestjs.io/docs/configuration#testenvironment-string 
