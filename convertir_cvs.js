const shapefile = require('shapefile');
const createCsvWriter = require('csv-writer').createObjectCsvWriter;
const fs = require('fs');
const path = require('path');

// Busca pares SHP/DBF recursivamente en subcarpetas
function findShpDbfPairsRecursive(dir) {
  let pairs = [];
  const files = fs.readdirSync(dir);
  files.forEach(f => {
    const fullPath = path.join(dir, f);
    if (fs.statSync(fullPath).isDirectory()) {
      pairs = pairs.concat(findShpDbfPairsRecursive(fullPath));
    } else if (f.endsWith('.shp')) {
      const stem = path.basename(f, '.shp');
      const dbfPath = path.join(dir, stem + '.dbf');
      if (fs.existsSync(dbfPath)) {
        pairs.push({
          shp: fullPath,
          dbf: dbfPath,
          name: path.join(path.basename(dir), stem)
        });
      }
    }
  });
  return pairs;
}

async function processPair(pair) {
  let headers = null;
  const records = [];
  await shapefile.open(pair.shp, pair.dbf)
    .then(source => source.read()
      .then(function next(result) {
        if (result.done) return;
        if (!headers) {
          headers = Object.keys(result.value.properties).map(k => ({id: k, title: k}));
        }
        records.push(result.value.properties);
        return source.read().then(next);
      }))
    .catch(error => console.error(`Error en ${pair.name}:`, error.stack));
  if (records.length === 0) {
    console.log(`Nada para ${pair.name}`);
    return;
  }
  const csvPath = path.join(process.cwd(), `${pair.name}.csv`);
  const csvWriter = createCsvWriter({path: csvPath, header: headers});
  await csvWriter.writeRecords(records);
  console.log(`Exportado: ${csvPath}`);
}

async function main() {
  const baseDir = process.cwd();
  const pairs = findShpDbfPairsRecursive(baseDir);
  if (pairs.length === 0) {
    console.log('No hay pares SHP/DBF encontrados en subcarpetas');
    return;
  }
  for (const pair of pairs) {
    await processPair(pair);
  }
  console.log('¡Automatización de subcarpetas completa!');
}

main();
