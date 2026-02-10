local Rayfield = loadstring(game:HttpGet('https://sirius.menu/rayfield'))()

local Window = Rayfield:CreateWindow({
   Name = "Đường Ray Chết [Beta] | Anhnguyendz882",
   LoadingTitle = "Đang tải hệ thống Bypass...",
   LoadingSubtitle = "Vui lòng chờ giây lát",
   ConfigurationSaving = { Enabled = false }
})

-- TAB CHÍNH: CHIẾN ĐẤU
local CombatTab = Window:CreateTab("Chiến Đấu", 4483362458)

CombatTab:CreateSection("Aimbot & FOV")

local FOVConfig = {
    Enabled = false,
    Radius = 150,
    Color = Color3.fromRGB(255, 0, 0)
}

-- Vẽ vòng tròn FOV
local fovCircle = Drawing.new("Circle")
fovCircle.Visible = false
fovCircle.Thickness = 1
fovCircle.Color = FOVConfig.Color
fovCircle.Filled = false

CombatTab:CreateToggle({
   Name = "Bật Silent Aim (Auto Dính)",
   CurrentValue = false,
   Callback = function(Value)
      _G.SilentAim = Value
      fovCircle.Visible = Value
   end,
})

CombatTab:CreateSlider({
   Name = "Phạm vi FOV (Vòng tròn bắn)",
   Range = {50, 500},
   Increment = 1,
   CurrentValue = 150,
   Callback = function(Value)
      FOVConfig.Radius = Value
      fovCircle.Radius = Value
   end,
})

-- TAB TIỆN ÍCH: NHÌN XUYÊN (ESP)
local ESPTab = Window:CreateTab("Tài Nguyên & ESP", 4483362458)

ESPTab:CreateSection("Lọc Vật Phẩm (Chống Lag)")

local function ApplyESP(object, color, name)
    if not object:FindFirstChild("ESPHighlight") then
        local hl = Instance.new("Highlight", object)
        hl.Name = "ESPHighlight"
        hl.FillColor = color
        hl.OutlineColor = Color3.fromRGB(255, 255, 255)
        
        local bill = Instance.new("BillboardGui", object)
        bill.AlwaysOnTop = true
        bill.Size = UDim2.new(0, 50, 0, 20)
        local lbl = Instance.new("TextLabel", bill)
        lbl.Text = name
        lbl.TextColor3 = color
        lbl.BackgroundTransparency = 1
        lbl.Size = UDim2.new(1,0,1,0)
        lbl.TextScaled = true
    end
end

ESPTab:CreateButton({
   Name = "Quét Tài Nguyên (Than, Vật Liệu, Chất Cháy)",
   Callback = function()
      for _, v in pairs(game.Workspace:GetDescendants()) do
          if v:IsA("Part") or v:IsA("MeshPart") then
              -- Kiểm tra tên vật phẩm (Dựa trên tên phổ biến trong game)
              if v.Name:find("Coal") or v.Name:find("Than") then
                  ApplyESP(v, Color3.fromRGB(50, 50, 50), "Than")
              elseif v.Name:find("Material") or v.Name:find("Wood") then
                  ApplyESP(v, Color3.fromRGB(139, 69, 19), "Vật liệu")
              elseif v.Name:find("Fuel") or v.Name:find("Gas") then
                  ApplyESP(v, Color3.fromRGB(255, 165, 0), "Chất cháy")
              end
          end
      end
      Rayfield:Notify({Title = "Xong", Content = "Đã đánh dấu tài nguyên gần nhất", Duration = 3})
   end,
})

-- TAB NHÂN VẬT & BYPASS
local MiscTab = Window:CreateTab("Nhân Vật", 4483362458)

MiscTab:CreateToggle({
   Name = "FullBright (Sáng Đêm)",
   CurrentValue = false,
   Callback = function(Value)
      if Value then
          game.Lighting.Ambient = Color3.fromRGB(255, 255, 255)
          game.Lighting.Brightness = 2
      else
          game.Lighting.Ambient = Color3.fromRGB(127, 127, 127)
      end
   end,
})

MiscTab:CreateToggle({
   Name = "Đi Xuyên Tường (Noclip)",
   CurrentValue = false,
   Callback = function(Value)
      _G.Noclip = Value
      game:GetService("RunService").Stepped:Connect(function()
          if _G.Noclip then
              for _, v in pairs(game.Players.LocalPlayer.Character:GetDescendants()) do
                  if v:IsA("BasePart") then v.CanCollide = false end
              end
          end
      end)
   end,
})

MiscTab:CreateSection("Bypass Anti-Cheat")

MiscTab:CreateButton({
   Name = "Bypass Speed/Jump Check",
   Callback = function()
      -- Kỹ thuật Bypass cơ bản bằng cách can thiệp vào Metatable
      local mt = getrawmetatable(game)
      setreadonly(mt, false)
      local oldIndex = mt.__index
      mt.__index = newcclosure(function(t, k)
          if k == "WalkSpeed" or k == "JumpPower" then return 16 end
          return oldIndex(t, k)
      end)
      setreadonly(mt, true)
      Rayfield:Notify({Title = "Bypass", Content = "Đã kích hoạt chống quét tốc độ", Duration = 5})
   end,
})

-- Logic Aimbot (Chạy ngầm)
game:GetService("RunService").RenderStepped:Connect(function()
    fovCircle.Position = game:GetService("UserInputService"):GetMouseLocation()
    if _G.SilentAim then
        -- Logic tìm quái vật gần nhất trong FOV sẽ nằm ở đây
    end
end)
