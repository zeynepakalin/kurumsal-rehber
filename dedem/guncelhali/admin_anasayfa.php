<?php
session_start();
if (!isset($_SESSION['superadmin_role']) || $_SESSION['superadmin_role'] != "superadmin") {
    header("Location: login.php");
    exit();
}

include("vt.php"); // Tek bağlantı buradan geliyor ($baglanti)

$baglanti->set_charset("utf8");

// Kullanıcı ekleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; 
    $role = $_POST['role'];

    $checkStmt = $baglanti->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<script>alert('Bu kullanıcı adı zaten alınmış. Lütfen başka bir kullanıcı adı seçin.');
        window.location.href = 'admin_kullanicilar.php';
        </script>";
    } else {
        $stmt = $baglanti->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);
        if ($stmt->execute()) {
            echo "<script>alert('Yeni kullanıcı başarıyla eklendi!');
            window.location.href = 'admin_kullanicilar.php';
            </script>";
        } else {
            echo "<script>alert('Hata: Kullanıcı eklenemedi.');
            window.location.href = 'admin_kullanicilar.php';
            </script>";
        }
    }
}

// Kullanıcı silme işlemi
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $baglanti->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Kullanıcı başarıyla silindi!');
         window.location.href = 'admin_kullanicilar.php';
              </script>";
    } else {
        echo "<script>alert('Hata: Kullanıcı silinemedi.');
        window.location.href = 'admin_kullanicilar.php';
        </script>";
    }
}

// Kullanıcı düzenleme işlemi (Form doldurma)
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $baglanti->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editResult = $stmt->get_result();
    if ($editResult->num_rows == 1) {
        $editRow = $editResult->fetch_assoc();
        $editUsername = $editRow['username'];
        $editPassword = $editRow['password'];
        $editRole = $editRow['role'];
    }
}

// Kullanıcı düzenleme işlemi (Güncelleme)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $id = (int)$_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $baglanti->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $password, $role, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Kullanıcı başarıyla güncellendi!');
         window.location.href = 'admin_kullanicilar.php';
         </script>";
    } else {
        echo "<script>alert('Hata: Kullanıcı güncellenemedi.');
         window.location.href = 'admin_kullanicilar.php';
         </script>";
    }
}

// Kullanıcıları çek
$result = $baglanti->query("SELECT id, username, role FROM users");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcılar</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>Menü</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_anasayfa.php">Anasayfa</a></li>
                <li><a href="admin_kullanicilar.php">Kullanıcılar</a></li>
            </ul>
        </nav>
        <div class="main-content">
            <header class="topbar">
                <h1>Kullanıcılar</h1>
                <div class="user-info">
                    <p>Hoşgeldin, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                    <a href="logout.php">Logout</a>
                </div>
            </header>
            <div class="content">
                <form method="POST" action="admin_kullanicilar.php">
                    <input type="hidden" name="id" value="<?php echo isset($editRow) ? $editRow['id'] : ''; ?>">
                    <div class="form-group">
                        <label for="username">Kullanıcı Adı:</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo isset($editUsername) ? htmlspecialchars($editUsername) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Parola:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Rol:</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="admin" <?php echo (isset($editRole) && $editRole == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="superadmin" <?php echo (isset($editRole) && $editRole == 'superadmin') ? 'selected' : ''; ?>>Superadmin</option>
                        </select>
                    </div>
                    <?php if (isset($editRow)): ?>
                        <button type="submit" name="update_user" class="btn btn-primary">Güncelle</button>
                    <?php else: ?>
                        <button type="submit" name="add_user" class="btn btn-primary">Kullanıcı Ekle</button>
                    <?php endif; ?>
                </form>

                <h2>Eklenen Kullanıcılar</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı Adı</th>
                            <th>Rol</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>" . htmlspecialchars($row['username']) . "</td>
                                        <td>{$row['role']}</td>
                                        <td>
                                            <a href='admin_kullanicilar.php?edit={$row['id']}' class='btn btn-warning btn-sm'>Düzenle</a>
                                            <a href='admin_kullanicilar.php?delete={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Bu kullanıcıyı silmek istediğinizden emin misiniz?\");'>Sil</a>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>Hiçbir kullanıcı bulunamadı.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
