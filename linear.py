# Required packages
import mysql.connector
import pandas as pd
from datetime import datetime
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression
from sqlalchemy import create_engine  # Import SQLAlchemy for database connection
import numpy as np

# MySQL database connection string (replace with your details)
db_connection_str = 'mysql+mysqlconnector://<username>:<password>@<hostname>/<database_name>'

# Create SQLAlchemy engine
engine = create_engine(db_connection_str)

try:
    # Connect to the database using mysql.connector
    conn = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='esa_hp'
    )

    # Fetch historical expenses data
    query_expenses = """
    SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total_expense
    FROM Expenses
    GROUP BY month
    ORDER BY month
    """
    expenses_df = pd.read_sql(query_expenses, con=engine)

    # Fetch current month's budget limit
    user_id = 1  # Replace with dynamic user_id
    current_month = datetime.now().month
    current_year = datetime.now().year
    query_budget = """
    SELECT SUM(amount_limit) as total_budget
    FROM Budgets
    WHERE user_id = %s AND month = %s AND year = %s
    """
    budget_df = pd.read_sql(query_budget, con=engine, params=(user_id, current_month, current_year))

    # Close the connection
    conn.close()

    # Prepare data
    expenses_df['month'] = pd.to_datetime(expenses_df['month'])
    expenses_df['month_num'] = expenses_df['month'].dt.month
    X = expenses_df[['month_num']]
    y = expenses_df['total_expense']

    # Check if enough data points for split
    if len(X) > 1:
        # Train-test split
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

        # Train linear regression model
        model = LinearRegression()
        model.fit(X_train, y_train)

        # Predict expenses for the current month
        current_month_num = np.array([[current_month]])
        predicted_expense = model.predict(current_month_num)[0]
        total_budget = budget_df['total_budget'].iloc[0]

        # Output results
        print(f"Predicted Expense for the current month: {predicted_expense}")
        print(f"Total Budget for the current month: {total_budget}")

        if predicted_expense <= total_budget:
            print("The budget limit is sufficient for the current month.")
        else:
            print("The budget limit is not sufficient for the current month.")

    else:
        print("Not enough data points to perform train-test split.")

except Exception as e:
    print(f"An error occurred: {e}")

finally:
    # Dispose SQLAlchemy engine
    engine.dispose()
