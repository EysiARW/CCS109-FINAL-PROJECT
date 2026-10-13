<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Sign in — CuyoTech SSIS') ?></title>
  <link rel="icon" type="image/png" href="CuyoTech_University_Logo.png">
  <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__ . '/../../assets/style.css') ?>">
</head>
<body>
<?= $content ?>
<script>
function togglePw(btn){
  var inp = document.getElementById('password');
  if(inp.type === 'password'){ inp.type = 'text'; btn.textContent = 'Hide'; }
  else { inp.type = 'password'; btn.textContent = 'Show'; }
}
</script>
</body>
</html>
