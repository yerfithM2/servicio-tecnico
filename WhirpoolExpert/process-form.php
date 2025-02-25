<?php
header('Content-Type: application/json');

// Configuración para recibir las imágenes
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Procesar las imágenes
$imageUrls = [];
if (!empty($_FILES)) {
    foreach ($_FILES as $key => $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $tempName = $file['tmp_name'];
            $fileName = uniqid() . '_' . basename($file['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($tempName, $targetPath)) {
                $imageUrls[$key] = $targetPath;
            }
        }
    }
}

// Recoger datos del formulario
$formData = json_decode($_POST['formData'], true);

// Preparar el mensaje de correo
$to = $formData['contact']['email'];
$subject = "Confirmación de Cita - WhirlpoolExpert";

$message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { text-align: center; padding: 20px; background: #f3f4f6; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>¡Tu cita ha sido confirmada!</h1>
        </div>
        <div class='content'>
            <h2>Detalles de tu cita:</h2>
            <p><strong>Servicio:</strong> " . htmlspecialchars($formData['service']) . "</p>
            <p><strong>Electrodoméstico:</strong> " . htmlspecialchars($formData['appliance']['type']) . "</p>
            <p><strong>Marca:</strong> " . htmlspecialchars($formData['appliance']['brand']) . "</p>
            <p><strong>Fecha:</strong> " . htmlspecialchars($formData['datetime']['date']) . "</p>
            <p><strong>Horario:</strong> " . htmlspecialchars($formData['datetime']['timeSlot']) . "</p>
            <p><strong>Nombre:</strong> " . htmlspecialchars($formData['contact']['name']) . "</p>
            <p><strong>Teléfono:</strong> " . htmlspecialchars($formData['contact']['phone']) . "</p>
            <p><strong>Dirección:</strong> " . htmlspecialchars($formData['contact']['address']) . "</p>
            <p><strong>Ciudad:</strong> " . htmlspecialchars($formData['contact']['city']) . "</p>
            <p><strong>Referencias:</strong> " . htmlspecialchars($formData['contact']['references']) . "</p>
        </div>
        <div class='footer'>
            <p>Gracias por confiar en WhirlpoolExpert</p>
            <p>Si necesitas modificar tu cita, por favor contáctanos</p>
        </div>
    </div>
</body>
</html>
";

// Cabeceras para enviar HTML
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: WhirlpoolExpert <noreply@whirlpoolexpert.com>' . "\r\n";

// Enviar correo al cliente
$mailSent = mail($to, $subject, $message, $headers);

// Enviar correo al administrador
$adminEmail = "tu@email.com"; // Cambia esto por tu email
$adminSubject = "Nueva Cita Programada - WhirlpoolExpert";
mail($adminEmail, $adminSubject, $message, $headers);

// Responder al cliente
echo json_encode([
    'success' => $mailSent,
    'message' => $mailSent ? 'Cita programada con éxito' : 'Error al enviar el correo',
    'imageUrls' => $imageUrls
]);
?>