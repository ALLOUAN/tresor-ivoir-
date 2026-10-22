// Régénère public/css/tw-<groupe>.css. À relancer quand de nouvelles classes Tailwind sont ajoutées dans les vues :
//   cd tools/tailwind && npm install && npm run build
// Chaque fichier configs/<groupe>.json = la configuration Tailwind propre à un groupe de pages (couleurs, animations).
const fs = require('fs'), path = require('path'), { execFileSync } = require('child_process');
const root = path.resolve(__dirname, '../..');
const content = [path.join(root, 'resources/views/**/*.blade.php'), path.join(root, 'resources/js/**/*.js'), path.join(root, 'app/**/*.php')];
for (const f of fs.readdirSync(path.join(__dirname, 'configs')).filter(f => f.endsWith('.json'))) {
    const key = f.replace('.json', '');
    const { config } = JSON.parse(fs.readFileSync(path.join(__dirname, 'configs', f), 'utf8'));
    const cfgFile = path.join(__dirname, `.cfg-${key}.js`);
    fs.writeFileSync(cfgFile, 'module.exports = ' + JSON.stringify({ content, ...config }));
    execFileSync(process.execPath, [path.join(__dirname, 'node_modules/tailwindcss/lib/cli.js'), '-c', cfgFile, '-i', path.join(__dirname, 'in.css'), '-o', path.join(root, `public/css/tw-${key}.css`), '--minify'], { stdio: 'inherit' });
    fs.unlinkSync(cfgFile);
    console.log('ok', key);
}
