// Offline regression tests for the datatable transpose utility.
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

const php = process.argv[2] || 'php';
const script = path.resolve(__dirname, '..', 'transpose.php');
const fixtureRoot = fs.mkdtempSync(path.join(__dirname, 'tmp-transpose-'));

function runTranspose(input) {
  return spawnSync(php, [script, input], { cwd: fixtureRoot, encoding: 'utf8' });
}

try {
  // A valid suffix must be recognized from the filename, not an earlier directory name.
  const nested = path.join(fixtureRoot, '_transpose.csv');
  fs.mkdirSync(nested);
  fs.writeFileSync(path.join(nested, 'valid_transpose.csv'), 'name,Ada,Lin\ncity,Singapore,Toronto\n');
  let result = runTranspose('_transpose.csv/valid_transpose.csv');
  assert.strictEqual(result.error, undefined, `could not run transpose.php: ${result.error}`);
  assert.strictEqual(result.stdout, '', `valid input was rejected: ${result.stdout}${result.stderr}`);
  assert.strictEqual(result.stderr, '', `valid input emitted an error: ${result.stderr}`);
  const transposed = fs.readFileSync(path.join(nested, 'valid.csv'), 'utf8').replace(/\r\n/g, '\n');
  assert.strictEqual(transposed, 'iteration,1\nname,city\nAda,Singapore\nLin,Toronto\n');

  // A substring is insufficient: reject non-suffix inputs without touching their paired path.
  fs.writeFileSync(path.join(fixtureRoot, 'backup_transpose.csv.bak'), 'field,value\n');
  fs.writeFileSync(path.join(fixtureRoot, 'backup.csv.bak'), 'keep\n');
  result = runTranspose(path.join(fixtureRoot, 'backup_transpose.csv.bak'));
  assert.strictEqual(result.error, undefined, `could not run transpose.php: ${result.error}`);
  assert.ok(/must end with '_transpose\.csv'/.test(`${result.stdout}${result.stderr}`), 'non-suffix input was not rejected');
  assert.strictEqual(fs.readFileSync(path.join(fixtureRoot, 'backup.csv.bak'), 'utf8'), 'keep\n', 'invalid input overwrote the paired sentinel file');

  process.stdout.write('PASS: transpose utility synthetic CSV regressions\n');
} finally {
  fs.rmSync(fixtureRoot, { recursive: true, force: true });
}
