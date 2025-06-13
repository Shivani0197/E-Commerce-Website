import pandas as pd
from prophet import Prophet

# Load the sales data
df = pd.read_csv("sales_data.csv")

# Convert date column to datetime format
df["date"] = pd.to_datetime(df["date"])

# Prepare data for Prophet (rename columns)
df = df.rename(columns={"date": "ds", "sales": "y"})

# Train the Prophet model
model = Prophet()
model.fit(df)

# Predict for the next 30 days
future = model.make_future_dataframe(periods=30)
forecast = model.predict(future)

# Save forecast data
forecast[['ds', 'yhat']].to_csv("price_forecast.csv", index=False)
print("Forecast saved successfully!")
